<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Category;
use App\Models\Subcategory;
use App\Models\Story;
use Illuminate\Support\Str;

class InitialContentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = [
            ['slug' => 'recommended', 'label' => 'PREPORUČENO', 'subcategories' => ['✨ Sve', '👤 Za vas', '🔥 Popularno', '🆕 Novo', '📈 Trendovi', '🧘‍♀️ Umirujuće', '⌛ Duge', '⏱️ Kratke', '🏆 Najbolje', '❤️‍🩹 Favoriti']],
            ['slug' => 'stories', 'label' => 'PRIČE', 'subcategories' => ['📚 Klasici', '🕵️ Misterija', '🧚 Bajke', '⌛ Istorija', '🏙️ Moderna', '🎨 Avantura', '🎭 Drama', '🧝 Fantazija', '💖 Romantika', '🔍 Krimić']],
            ['slug' => 'meditations', 'label' => 'MEDITACIJE', 'subcategories' => ['🧘 Osnovno', '🌍 Stres', '🌐 Anksioznost', '🎯 Fokus', '😊 Sreća', '🙏 Zahvalnost', '💪 Samopouzdanje', '🎭 Emocije', '👫 Odnosi', '🍼 Rad']],
            ['slug' => 'kids', 'label' => 'DJECA', 'subcategories' => ['👶 Bebe', '🧒 Mališani', '🏫 Predškolci', '📚 Školarci', '🧙 Avanture', '😴 Uspavanke', '📖 Edukativno', '🐾 Životinje', '✨ Čarolija', '🦸 Heroji']],
            ['slug' => 'mythology', 'label' => 'MITOLOGIJA', 'subcategories' => ['🏛️ Grčka', '🛡️ Rimljani', '❄️ Nordijska', '🦂 Egipat', '♻️ Azija', '🍀 Keltska', '📜 Slavenska', '☀️ Asteci', '📜 Legende', '⚡ Bogovi']],
            ['slug' => 'nature', 'label' => 'PRIRODA', 'subcategories' => ['🌲 Šuma', '🌊 Okean', '🏔️ Planine', '🏜️ Pustinja', '💧 Rijeke', '☔ Kiša', '❄️ Snijeg', '🌼 Livade', '🕳️ Pećine', '🌌 Svemir']],
            ['slug' => 'asmr', 'label' => 'ASMR', 'subcategories' => ['🤫 Šapat', '🫱 Tapiranje', '💇 Češljanje', '🚿 Voda', '🍎 Hrskanje', '📖 Stranice', '⌨️ Kuckanje', '🪣 Pozitiva', '👂 Blisko', '🪄 Pribor']],
            ['slug' => 'music', 'label' => 'MUZIKA', 'subcategories' => ['🎹 Klavir', '🎸 Gitara', '☁️ Ambience', '☕ Lo-Fi', '🎼 Klasika', '🎶 Harfa', '🎛️ Sintezajzer', '🥁 Udaraljke', '🎤 Vokal', '🎧 Binauralno']],
            ['slug' => 'scifi', 'label' => 'NAUČNA FANT.', 'subcategories' => ['🚀 Svemir', '🤖 Roboti', '🛰️ Budućnost', '🌐 Vrijeme', '🌌 Galaksije', '⭐ Zvijezde', '🪐 Planete', '☣️ Apokalipsa', '💻 Tehnologija', '🌃 Cyberpunk']],
            ['slug' => 'travel', 'label' => 'PUTOVANJA', 'subcategories' => ['🇪🇺 Evropa', '🌏 Azija', '🌍 Afrika', '🌎 Amerika', '🇦🇺 Australija', '🏙️ Gradovi', '🏡 Sela', '🏝️ Ostrva', '🚂 Vlak', '✈️ Avion']],
        ];

        $categoryMap = [];
        foreach ($categories as $order => $cat) {
            $category = Category::updateOrCreate(
                ['slug' => $cat['slug']],
                [
                    'label' => $cat['label'],
                    'description' => $cat['description'] ?? null,
                    'is_active' => true,
                ]
            );

            $categoryMap[$cat['slug']] = $category;

            foreach ($cat['subcategories'] ?? [] as $subOrder => $subLabel) {
                $slug = Str::slug(preg_replace('/[^\p{L}\p{N}]+/u', ' ', $subLabel));
                if (!$slug) {
                    $slug = 'sub-' . $subOrder;
                }
                Subcategory::updateOrCreate(
                    ['category_id' => $category->id, 'slug' => $slug],
                    [
                        'label' => $subLabel,
                        'is_active' => true,
                    ]
                );
            }
        }

        $subLookup = [];
        $subs = Subcategory::with('category')->get();
        foreach ($subs as $sub) {
            $slugKey = $sub->category?->slug;
            if ($slugKey) {
                $subLookup[strtolower($slugKey)][$sub->label] = $sub->id;
            }
        }

        $stories = [
            [
                'slug' => 'breathing-woods',
                'title' => 'Šuma koja diše',
                'narrator' => 'Ambient Forest',
                'duration' => '32 min',
                'image' => 'https://images.unsplash.com/photo-1502082553048-f009c37129b9?auto=format&fit=crop&q=80&w=800',
                'description' => 'Duboko disanje uz zvukove kišne šume i nježni šum lišća.',
                'category' => 'nature',
                'subcategory' => '🌲 Šuma',
                'is_dummy' => true,
                'effects' => ['ocean' => 0, 'rain' => 0, 'fire' => 2, 'leaves' => 8, 'river' => 0, 'birds' => 8, 'fan' => 0, 'snow' => 0, 'train' => 0, 'crickets' => 0],
            ],
            [
                'slug' => 'great-bird',
                'title' => 'VELIKA PTICA',
                'narrator' => 'Elizabeth Grace',
                'duration' => '38 min',
                'image' => 'https://images.unsplash.com/photo-1500530855697-b586d89ba3ee?auto=format&fit=crop&q=80&w=800',
                'description' => 'Magično putovanje na leđima mitskog bića kroz oblake.',
                'category' => 'kids',
                'subcategory' => '✨ Čarolija',
                'is_dummy' => true,
                'locked' => true,
                'effects' => ['ocean' => 0, 'rain' => 2, 'fire' => 0, 'leaves' => 6, 'river' => 2, 'birds' => 9, 'fan' => 0, 'snow' => 0, 'train' => 1, 'crickets' => 4],
            ],
            [
                'slug' => 'rest-room',
                'title' => 'Tajna soba za odmor',
                'narrator' => 'TK Kellman',
                'duration' => '23 min',
                'image' => 'https://images.unsplash.com/photo-1513694203232-719a280e022f?auto=format&fit=crop&q=80&w=800',
                'description' => 'Pronađite mir u skrivenom utočištu dizajniranom samo za opuštanje.',
                'category' => 'meditations',
                'subcategory' => '🌍 Stres',
                'is_dummy' => false,
                'effects' => ['ocean' => 0, 'rain' => 4, 'fire' => 5, 'leaves' => 3, 'river' => 0, 'birds' => 2, 'fan' => 6, 'snow' => 0, 'train' => 1, 'crickets' => 0],
            ],
            [
                'slug' => 'saturn',
                'title' => 'KATAPULTIRANJE OKO SATURNA',
                'narrator' => 'Thomas Jones',
                'duration' => '38 min',
                'image' => 'https://images.unsplash.com/photo-1614732414444-096e5f1122d5?auto=format&fit=crop&q=80&w=800',
                'description' => 'Istražite veličanstvene prstenove Saturna i njegove mjesece.',
                'category' => 'scifi',
                'subcategory' => '🚀 Svemir',
                'is_dummy' => true,
                'locked' => true,
                'effects' => ['ocean' => 0, 'rain' => 0, 'fire' => 2, 'leaves' => 0, 'river' => 0, 'birds' => 1, 'fan' => 3, 'snow' => 0, 'train' => 0, 'crickets' => 1],
            ],
            [
                'slug' => 'greek-villa',
                'title' => 'SANJIV DAN U ANTIČKOJ GRČKOJ VILI',
                'narrator' => 'Abbe Opher',
                'duration' => '38 min',
                'image' => 'https://images.unsplash.com/photo-1505761671935-60b3a7427bad?auto=format&fit=crop&q=80&w=800',
                'description' => 'Povratak u antičko doba u luksuznoj vili pored Egejskog mora.',
                'category' => 'mythology',
                'subcategory' => '🏛️ Grčka',
                'is_dummy' => true,
                'effects' => ['ocean' => 8, 'rain' => 1, 'fire' => 0, 'leaves' => 3, 'river' => 2, 'birds' => 5, 'fan' => 1, 'snow' => 0, 'train' => 0, 'crickets' => 4],
            ],
            [
                'slug' => 'kids-forest',
                'title' => 'Prijatelji iz šume',
                'narrator' => 'Elizabeth Grace',
                'duration' => '25 min',
                'image' => 'https://images.unsplash.com/photo-1441974231531-c6227db76b6e?auto=format&fit=crop&q=80&w=800',
                'description' => 'Upoznajte vesele stanovnike šume u ovoj priči za najmlađe.',
                'category' => 'kids',
                'subcategory' => '🐾 Životinje',
                'is_dummy' => true,
                'effects' => ['ocean' => 1, 'rain' => 3, 'fire' => 1, 'leaves' => 9, 'river' => 3, 'birds' => 8, 'fan' => 0, 'snow' => 0, 'train' => 0, 'crickets' => 7],
            ],
            [
                'slug' => 'london-sleep',
                'title' => 'ISTORIJA SNA U LONDONU',
                'narrator' => 'Thomas Jones',
                'duration' => '55 min',
                'image' => 'https://images.unsplash.com/photo-1513635269975-59663e0ac1ad?auto=format&fit=crop&q=80&w=800',
                'description' => 'Prošetajte kroz maglovite ulice istorijskog Londona.',
                'category' => 'stories',
                'subcategory' => '⌛ Istorija',
                'is_dummy' => true,
                'effects' => ['ocean' => 0, 'rain' => 8, 'fire' => 0, 'leaves' => 0, 'river' => 0, 'birds' => 2, 'fan' => 0, 'snow' => 1, 'train' => 4, 'crickets' => 0],
            ],
            [
                'slug' => 'santa-claus',
                'title' => 'ŽIVOT I AVANTURE DJEDA MRAZA',
                'narrator' => 'L. Frank Baum',
                'duration' => '9 poglavlja',
                'image' => 'https://images.unsplash.com/photo-1543589077-47d81606c1bf?auto=format&fit=crop&q=80&w=800',
                'description' => 'Kompletna zbirka priča o porijeklu božićne čarolije.',
                'category' => 'stories',
                'subcategory' => '📚 Klasici',
                'is_dummy' => true,
                'effects' => ['ocean' => 0, 'rain' => 0, 'fire' => 4, 'leaves' => 0, 'river' => 0, 'birds' => 1, 'fan' => 1, 'snow' => 9, 'train' => 0, 'crickets' => 1],
            ],
            [
                'slug' => 'deep-focus',
                'title' => 'DUBOKI FOKUS',
                'narrator' => 'Ambient Music',
                'duration' => '60 min',
                'image' => 'https://images.unsplash.com/photo-1456513080510-7bf3a84b82f8?auto=format&fit=crop&q=80&w=800',
                'description' => 'Savršena muzička podloga za duboko učenje i koncentraciju.',
                'category' => 'music',
                'subcategory' => '☁️ Ambience',
                'is_dummy' => true,
                'effects' => ['ocean' => 3, 'rain' => 3, 'fire' => 1, 'leaves' => 1, 'river' => 3, 'birds' => 1, 'fan' => 5, 'snow' => 0, 'train' => 0, 'crickets' => 0],
            ],
            [
                'slug' => 'lofi-study',
                'title' => 'LO-FI ZA UČENJE',
                'narrator' => 'Chill Beats',
                'duration' => '45 min',
                'image' => 'https://images.unsplash.com/photo-1516280440614-37939bbacd81?auto=format&fit=crop&q=80&w=800',
                'description' => 'Opuštajući ritmovi koji pomažu da ostanete fokusirani na zadatke.',
                'category' => 'music',
                'subcategory' => '☕ Lo-Fi',
                'is_dummy' => true,
                'effects' => ['ocean' => 2, 'rain' => 2, 'fire' => 1, 'leaves' => 1, 'river' => 2, 'birds' => 1, 'fan' => 6, 'snow' => 0, 'train' => 0, 'crickets' => 0],
            ],
            [
                'slug' => 'alpha-waves',
                'title' => 'ALFA TALASI',
                'narrator' => 'Brain Sync',
                'duration' => '30 min',
                'image' => 'https://images.unsplash.com/photo-1507413245164-6160d8298b31?auto=format&fit=crop&q=80&w=800',
                'description' => 'Binauralni zvukovi dizajnirani za optimizaciju moždanih funkcija tokom učenja.',
                'category' => 'music',
                'subcategory' => '🎧 Binauralno',
                'is_dummy' => true,
                'effects' => ['ocean' => 0, 'rain' => 0, 'fire' => 0, 'leaves' => 1, 'river' => 1, 'birds' => 1, 'fan' => 4, 'snow' => 0, 'train' => 0, 'crickets' => 1],
            ],
            [
                'slug' => 'moon-harbor',
                'title' => 'Luka pod mjesecom',
                'narrator' => 'Marin Luka',
                'duration' => '29 min',
                'image' => 'https://images.unsplash.com/photo-1507525428034-b723cf961d3e?auto=format&fit=crop&q=80&w=800',
                'description' => 'Noćno pristanište, talasi i daleki zvuk brodskih sirena.',
                'category' => 'NATURE',
                'subcategory' => '❄️ Snijeg',
                'is_dummy' => false,
                'effects' => ['ocean' => 7, 'rain' => 2, 'fire' => 1, 'leaves' => 1, 'river' => 2, 'birds' => 3, 'fan' => 0, 'snow' => 1, 'train' => 3, 'crickets' => 0],
            ],
            [
                'slug' => 'desert-stars',
                'title' => 'Zvijezde iznad pustinje',
                'narrator' => 'Layla Karim',
                'duration' => '34 min',
                'image' => 'https://images.unsplash.com/photo-1501785888041-af3ef285b470?auto=format&fit=crop&q=80&w=800',
                'description' => 'Pješčane dine pod mliječnim putem i šapat toplog vjetra.',
                'category' => 'NATURE',
                'subcategory' => '🏜️ Pustinja',
                'is_dummy' => true,
                'effects' => ['ocean' => 1, 'rain' => 0, 'fire' => 2, 'leaves' => 0, 'river' => 1, 'birds' => 0, 'fan' => 4, 'snow' => 0, 'train' => 1, 'crickets' => 8],
            ],
            [
                'slug' => 'northern-lullaby',
                'title' => 'Polarna uspavanka',
                'narrator' => 'Sanna Lehtinen',
                'duration' => '27 min',
                'image' => 'https://images.unsplash.com/photo-1500530855697-b586d89ba3ee?auto=format&fit=crop&q=80&w=800',
                'description' => 'Aurora borealis, tihe rijeke i škripa snijega pod nogama.',
                'category' => 'SCIFI',
                'subcategory' => '⭐ Zvijezde',
                'is_dummy' => true,
                'effects' => ['ocean' => 2, 'rain' => 0, 'fire' => 1, 'leaves' => 0, 'river' => 3, 'birds' => 0, 'fan' => 1, 'snow' => 8, 'train' => 1, 'crickets' => 0],
            ],
            [
                'slug' => 'aurora-train',
                'title' => 'Voz kroz polarno svitanje',
                'narrator' => 'Jonas Berg',
                'duration' => '33 min',
                'image' => 'https://images.unsplash.com/photo-1489515217757-5fd1be406fef?auto=format&fit=crop&q=80&w=800',
                'description' => 'Putovanje noćnim vozom dok zeleni velovi plešu na nebu.',
                'category' => 'TRAVEL',
                'subcategory' => '🚂 Vlak',
                'is_dummy' => true,
                'effects' => ['ocean' => 2, 'rain' => 2, 'fire' => 1, 'leaves' => 1, 'river' => 2, 'birds' => 1, 'fan' => 0, 'snow' => 5, 'train' => 9, 'crickets' => 0],
            ],
            [
                'slug' => 'lotus-pond',
                'title' => 'Lotosova laguna',
                'narrator' => 'Mira Chen',
                'duration' => '24 min',
                'image' => 'https://images.unsplash.com/photo-1501004318641-b39e6451bec6?auto=format&fit=crop&q=80&w=800',
                'description' => 'Kapi kiše na listovima lotosa i umirujući žamor žaba.',
                'category' => 'NATURE',
                'subcategory' => '☔ Kiša',
                'is_dummy' => true,
                'effects' => ['ocean' => 1, 'rain' => 7, 'fire' => 1, 'leaves' => 4, 'river' => 5, 'birds' => 4, 'fan' => 0, 'snow' => 0, 'train' => 0, 'crickets' => 6],
            ],
            [
                'slug' => 'silent-library',
                'title' => 'Tiha biblioteka',
                'narrator' => 'L. Frank Baum',
                'duration' => '11 poglavlja',
                'image' => 'https://images.unsplash.com/photo-1524995997946-a1c2e315a42f?auto=format&fit=crop&q=80&w=800',
                'description' => 'Zaboravljene knjige koje šapuću uspavanke iza zatvorenih vrata.',
                'category' => 'STORIES',
                'subcategory' => '📚 Klasici',
                'is_dummy' => true,
                'effects' => ['ocean' => 1, 'rain' => 0, 'fire' => 1, 'leaves' => 0, 'river' => 0, 'birds' => 0, 'fan' => 2, 'snow' => 0, 'train' => 1, 'crickets' => 1],
            ],
            [
                'slug' => 'crvenkapica',
                'title' => 'Crvenkapica',
                'narrator' => 'Amina Hasić',
                'duration' => '8 min',
                'image' => null,
                'description' => 'Klasik o djevojčici sa crvenom kapicom i susretu u šumi.',
                'category' => 'KIDS',
                'subcategory' => '🧚 Bajke',
                'is_dummy' => false,
                'effects' => ['ocean' => 0, 'rain' => 0, 'fire' => 5, 'leaves' => 0, 'river' => 0, 'birds' => 6, 'fan' => 0, 'snow' => 0, 'train' => 0, 'crickets' => 0],
            ],
        ];

        foreach ($stories as $order => $story) {
            $categoryKey = strtolower($story['category']);
            $category = $categoryMap[$categoryKey] ?? null;
            if (!$category) {
                continue;
            }

            $subcategoryId = $subLookup[$categoryKey][$story['subcategory']] ?? null;

            Story::updateOrCreate(
                ['slug' => $story['slug']],
                [
                    'title' => $story['title'],
                    'narrator' => $story['narrator'] ?? null,
                    'duration_label' => $story['duration'] ?? null,
                    'duration_seconds' => $this->durationSeconds($story['duration'] ?? null),
                    'image_url' => $story['image'],
                    'description' => $story['description'] ?? null,
                    'category_id' => $category->id,
                    'subcategory_id' => $subcategoryId,
                    'is_dummy' => $story['is_dummy'] ?? false,
                    'locked' => $story['locked'] ?? false,
                    'is_favorite' => $story['is_favorite'] ?? false,
                    'effects' => $story['effects'] ?? [],
                    'audio_url' => $story['audio_url'] ?? null,
                    'published_at' => now()->subDays($order),
                ]
            );
        }
    }

    private function durationSeconds(?string $label): ?int
    {
        if (!$label) {
            return null;
        }

        if (preg_match('/(\\d+)/', $label, $m)) {
            return (int) $m[1] * 60;
        }

        return null;
    }
}
