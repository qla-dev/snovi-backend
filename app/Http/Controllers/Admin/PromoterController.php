<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\GiftCode;
use App\Models\Promoter;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class PromoterController extends Controller
{
    public function index()
    {
        $promoters = Promoter::query()
            ->withCount([
                'giftCodes',
                'giftCodes as used_gift_codes_count' => fn ($query) => $query->where('used', true),
            ])
            ->orderByDesc('id')
            ->get();

        return view('admin.promoters.index', compact('promoters'));
    }

    public function create()
    {
        return redirect()->route('admin.promoters.index');
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);

        if ($request->hasFile('photo_upload')) {
            $data['photo_url'] = Storage::url($request->file('photo_upload')->store('promoters', 'public'));
        }

        $promoter = Promoter::create($data);

        return redirect()
            ->route('admin.promoters.edit', $promoter)
            ->with('status', 'Promoter je dodan. Dodijelite mu kodove.');
    }

    public function show(Promoter $promoter)
    {
        return redirect()->route('admin.promoters.edit', $promoter);
    }

    public function edit(Promoter $promoter)
    {
        // The promoter's own codes, plus admin codes that nobody has used or been given yet.
        $giftCodes = GiftCode::query()
            ->where(function ($query) use ($promoter) {
                $query->where('promoter_id', $promoter->id)
                    ->orWhere(function ($query) {
                        $query->whereNull('promoter_id')
                            ->whereNull('source')
                            ->where('used', false)
                            ->where(fn ($query) => $query->whereNull('expires_at')->orWhere('expires_at', '>', now()));
                    });
            })
            ->orderByRaw('CASE WHEN promoter_id IS NULL THEN 1 ELSE 0 END')
            ->orderByDesc('id')
            ->get();

        return view('admin.promoters.edit', compact('promoter', 'giftCodes'));
    }

    public function update(Request $request, Promoter $promoter)
    {
        $data = $this->validated($request, $promoter);

        if ($request->hasFile('photo_upload')) {
            $data['photo_url'] = Storage::url($request->file('photo_upload')->store('promoters', 'public'));
        } elseif ($request->boolean('remove_photo')) {
            $data['photo_url'] = null;
        }

        $assignment = $request->validate([
            'gift_code_ids' => ['nullable', 'array'],
            'gift_code_ids.*' => ['integer'],
            'new_codes' => ['nullable', 'integer', 'min:0', 'max:50'],
        ]);
        $codeIds = collect($assignment['gift_code_ids'] ?? [])->map(fn ($id) => (int) $id)->all();
        $newCodes = (int) ($assignment['new_codes'] ?? 0);

        DB::transaction(function () use ($promoter, $data, $codeIds, $newCodes) {
            $promoter->update($data);

            // Used codes stay with the promoter, so the page keeps counting them.
            $promoter->giftCodes()
                ->where('used', false)
                ->whereNotIn('id', $codeIds)
                ->update(['promoter_id' => null]);

            GiftCode::query()
                ->whereIn('id', $codeIds)
                ->whereNull('promoter_id')
                ->whereNull('source')
                ->where('used', false)
                ->update(['promoter_id' => $promoter->id]);

            for ($i = 0; $i < $newCodes; $i++) {
                GiftCode::query()->create([
                    'code' => GiftCode::generateUniqueCode(),
                    'promoter_id' => $promoter->id,
                    'used' => false,
                    'used_date' => null,
                    'expires_at' => now()->addYear(),
                ]);
            }
        });

        return redirect()
            ->route('admin.promoters.edit', $promoter)
            ->with('status', 'Promoter je ažuriran.');
    }

    public function destroy(Promoter $promoter)
    {
        $promoter->delete();

        return redirect()
            ->route('admin.promoters.index')
            ->with('status', 'Promoter je obrisan. Njegovi kodovi su ostali među gift kodovima.');
    }

    private function validated(Request $request, ?Promoter $promoter = null): array
    {
        $request->merge([
            'username' => Str::lower(trim((string) $request->input('username'))),
            'instagram' => ltrim(trim((string) $request->input('instagram')), '@') ?: null,
        ]);

        $data = $request->validate([
            'username' => ['required', 'string', 'max:60', 'regex:/^[a-z0-9][a-z0-9._-]*$/', Rule::unique('promoters', 'username')->ignore($promoter?->id)],
            'first_name' => ['required', 'string', 'max:80'],
            'last_name' => ['required', 'string', 'max:80'],
            'headline' => ['nullable', 'string', 'max:160'],
            'bio' => ['nullable', 'string', 'max:2000'],
            'instagram' => ['nullable', 'string', 'max:80', 'regex:/^[A-Za-z0-9._]+$/'],
            'photo_upload' => ['nullable', 'image', 'max:5120'],
            'is_active' => ['sometimes', 'boolean'],
        ], [
            'username.regex' => 'Username smije imati samo mala slova, brojeve, tačku, crticu i donju crtu.',
            'instagram.regex' => 'Instagram profil upišite bez linka, npr. ime.prezime.',
        ]);

        unset($data['photo_upload']);
        $data['is_active'] = $request->boolean('is_active');

        return $data;
    }
}
