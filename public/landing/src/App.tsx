/**
 * @license
 * SPDX-License-Identifier: Apache-2.0
 */

import React, { useState, useEffect, useRef } from 'react';
import { motion, AnimatePresence, useScroll, useTransform } from 'motion/react';
import { 
  Play, 
  Pause, 
  CloudRain, 
  Trees, 
  Waves, 
  Flame, 
  Download, 
  Globe, 
  ChevronRight, 
  Heart, 
  Brain, 
  Sparkles,
  Volume2,
  Smartphone,
  Apple,
  PlayCircle,
  Star,
  CheckCircle2,
  ArrowRight,
  Mail,
  Users
} from 'lucide-react';
import { theme } from './theme';
import { translations, Language } from './translations';

const stories = [
  { id: 1, title: { bs: 'Ružno pače', en: 'The Ugly Duckling' }, duration: '08:30', status: 'published', image: 'https://picsum.photos/seed/duckling/600/800' },
  { id: 2, title: { bs: 'Vuk i sedam kozlića', en: 'The Wolf and the Seven Kids' }, duration: '07:50', status: 'published', image: 'https://picsum.photos/seed/wolf/600/800' },
  { id: 3, title: { bs: 'Zlatokosa', en: 'Goldilocks' }, duration: '05:30', status: 'published', image: 'https://picsum.photos/seed/goldilocks/600/800' },
  { id: 4, title: { bs: 'Ivica i Marica', en: 'Hansel and Gretel' }, duration: '09:00', status: 'published', image: 'https://picsum.photos/seed/forest/600/800' },
  { id: 5, title: { bs: 'Snježna kraljica', en: 'The Snow Queen' }, duration: '--:--', status: 'comingSoon', image: 'https://picsum.photos/seed/snow/600/800' },
  { id: 6, title: { bs: 'Aladin', en: 'Aladdin' }, duration: '--:--', status: 'comingSoon', image: 'https://picsum.photos/seed/desert/600/800' },
];

const effects = [
  { id: 'rain', icon: CloudRain, label: { bs: 'Ljetna kiša', en: 'Summer Rain' }, color: 'text-blue-400' },
  { id: 'forest', icon: Trees, label: { bs: 'Noćna šuma', en: 'Night Forest' }, color: 'text-emerald-400' },
  { id: 'waves', icon: Waves, label: { bs: 'Valovi', en: 'Waves' }, color: 'text-cyan-400' },
  { id: 'fire', icon: Flame, label: { bs: 'Vatra', en: 'Fire' }, color: 'text-orange-400' },
];

export default function App() {
  const [lang, setLang] = useState<Language>('bs');
  const [activeSample, setActiveSample] = useState<number | null>(null);
  const [activeEffects, setActiveEffects] = useState<string[]>([]);
  const [scrolled, setScrolled] = useState(false);
  const t = translations[lang];

  const { scrollYProgress } = useScroll();
  const y1 = useTransform(scrollYProgress, [0, 1], [0, -200]);
  const y2 = useTransform(scrollYProgress, [0, 1], [0, 200]);

  useEffect(() => {
    const handleScroll = () => setScrolled(window.scrollY > 50);
    window.addEventListener('scroll', handleScroll);
    return () => window.removeEventListener('scroll', handleScroll);
  }, []);

  const toggleLang = () => setLang(prev => prev === 'bs' ? 'en' : 'bs');

  const toggleEffect = (id: string) => {
    setActiveEffects(prev => 
      prev.includes(id) ? prev.filter(e => e !== id) : [...prev, id]
    );
  };

  return (
    <div className="min-h-screen font-sans selection:bg-violet-500/30 bg-[#050505] text-white">
      {/* Navigation */}
      <nav className={`fixed top-0 w-full z-[100] transition-all duration-500 px-6 py-4 flex justify-between items-center ${scrolled ? 'glass border-b border-white/5 py-3' : 'bg-transparent'}`}>
        <div className="flex items-center gap-3">
          <motion.div 
            whileHover={{ rotate: 15 }}
            className="w-10 h-10 rounded-xl bg-gradient-to-br from-violet-500 to-indigo-600 flex items-center justify-center shadow-lg shadow-violet-500/20"
          >
            <Sparkles className="text-white w-6 h-6" />
          </motion.div>
          <span className="text-2xl font-bold tracking-tighter text-white">snovi<span className="text-violet-500">.fm</span></span>
        </div>
        
        <div className="hidden lg:flex items-center gap-10 text-[13px] uppercase tracking-widest font-bold text-slate-400">
          <a href="#psychology" className="hover:text-white transition-colors">{t.nav.psychology}</a>
          <a href="#effects" className="hover:text-white transition-colors">{t.nav.effects}</a>
          <a href="#stories" className="hover:text-white transition-colors">{t.nav.stories}</a>
        </div>

        <div className="flex items-center gap-4">
          <button 
            onClick={toggleLang}
            className="flex items-center gap-2 px-4 py-2 rounded-full glass hover:bg-white/10 transition-all text-[11px] font-black tracking-widest"
          >
            <Globe className="w-3 h-3 text-violet-400" />
            {lang.toUpperCase()}
          </button>
          <button 
            onClick={() => document.getElementById('waitlist')?.scrollIntoView({ behavior: 'smooth' })}
            className="hidden sm:flex px-6 py-2.5 bg-white text-black rounded-full text-xs font-black uppercase tracking-widest hover:bg-violet-500 hover:text-white transition-all"
          >
            {t.hero.cta}
          </button>
        </div>
      </nav>

      {/* Hero Section */}
      <section className="relative min-h-screen flex items-center pt-32 pb-20 overflow-hidden">
        {/* Atmospheric Backgrounds */}
        <div className="absolute top-0 left-0 w-full h-full -z-10">
          <div className="absolute top-[-20%] left-[-10%] w-[70%] h-[70%] bg-violet-600/10 blur-[180px] rounded-full" />
          <div className="absolute bottom-[-20%] right-[-10%] w-[70%] h-[70%] bg-indigo-600/10 blur-[180px] rounded-full" />
          <div className="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-full h-full bg-[url('https://www.transparenttextures.com/patterns/stardust.png')] opacity-20 pointer-events-none" />
          
          {/* Subtle Night Sky Sparkles */}
          <div className="absolute inset-0 overflow-hidden pointer-events-none">
            {[...Array(40)].map((_, i) => (
              <motion.div
                key={i}
                initial={{ 
                  opacity: Math.random() * 0.3,
                  x: `${Math.random() * 100}%`,
                  y: `${Math.random() * 100}%`,
                  scale: Math.random() * 0.5 + 0.5
                }}
                animate={{ 
                  opacity: [0.1, 0.5, 0.1],
                  scale: [1, 1.2, 1]
                }}
                transition={{ 
                  duration: 4 + Math.random() * 6,
                  repeat: Infinity,
                  delay: Math.random() * 10
                }}
                className="absolute w-0.5 h-0.5 bg-white rounded-full"
              />
            ))}
          </div>
        </div>

        <div className="max-w-7xl mx-auto px-6 w-full">
          <div className="flex flex-col items-center text-center mb-20">
            <motion.div 
              initial={{ opacity: 0, y: 20 }}
              animate={{ opacity: 1, y: 0 }}
              transition={{ duration: 0.8 }}
              className="inline-flex items-center gap-2 px-5 py-2 rounded-full bg-white/5 border border-white/10 text-violet-400 text-[11px] font-black uppercase tracking-[0.3em] mb-10"
            >
              <Sparkles className="w-3.5 h-3.5" />
              {t.hero.tagline}
            </motion.div>
            
            <motion.h1 
              initial={{ opacity: 0, y: 30 }}
              animate={{ opacity: 1, y: 0 }}
              transition={{ duration: 1, delay: 0.2 }}
              className="text-7xl md:text-[10rem] font-serif font-bold leading-[0.85] mb-10 tracking-tighter max-w-5xl"
            >
              {t.hero.title}
            </motion.h1>
            
            <motion.p 
              initial={{ opacity: 0, y: 20 }}
              animate={{ opacity: 1, y: 0 }}
              transition={{ duration: 1, delay: 0.4 }}
              className="text-xl md:text-2xl text-slate-400 max-w-2xl mb-10 leading-relaxed font-medium"
            >
              {t.hero.subtitle}
            </motion.p>

            <motion.div
              initial={{ opacity: 0, y: 20 }}
              animate={{ opacity: 1, y: 0 }}
              transition={{ duration: 1, delay: 0.5 }}
              className="flex flex-col md:flex-row items-center justify-center gap-6 w-full max-w-[320px] md:max-w-none mx-auto mb-14"
            >
              <button
                onClick={() => document.getElementById('waitlist')?.scrollIntoView({ behavior: 'smooth' })}
                className="w-full md:w-auto h-20 px-10 bg-violet-600 text-white rounded-2xl font-black uppercase tracking-widest hover:bg-white hover:text-black transition-all shadow-2xl shadow-violet-500/20 flex items-center justify-center gap-3 group"
              >
                {t.hero.cta}
                <ArrowRight className="w-5 h-5 group-hover:translate-x-1 transition-transform" />
              </button>
              
              <button className="w-full md:w-auto h-20 px-8 bg-white text-black hover:bg-violet-500 hover:text-white rounded-2xl font-black uppercase tracking-[0.2em] flex items-center justify-center gap-4 transition-all shadow-2xl shadow-white/10 group whitespace-nowrap">
                <Apple className="w-7 h-7" />
                <div className="text-left">
                  <p className="text-[10px] opacity-70 leading-none mb-1">{t.hero.download.appStore.split(' ')[0]}</p>
                  <p className="text-sm md:text-base leading-none">{t.hero.download.appStore.split(' ').slice(1).join(' ')}</p>
                </div>
              </button>

              <button className="w-full md:w-auto h-20 px-8 bg-white text-black hover:bg-violet-500 hover:text-white rounded-2xl font-black uppercase tracking-[0.2em] flex items-center justify-center gap-4 transition-all shadow-2xl shadow-white/10 group whitespace-nowrap">
                <PlayCircle className="w-7 h-7" />
                <div className="text-left">
                  <p className="text-[10px] opacity-70 leading-none mb-1">{t.hero.download.googlePlay.split(' ')[0]}</p>
                  <p className="text-sm md:text-base leading-none">{t.hero.download.googlePlay.split(' ').slice(1).join(' ')}</p>
                </div>
              </button>
            </motion.div>
          </div>

          {/* Device Mockups - Responsive Grid */}
          <motion.div 
            initial={{ opacity: 0, y: 100 }}
            animate={{ opacity: 1, y: 0 }}
            transition={{ duration: 1.5, delay: 0.8, ease: [0.16, 1, 0.3, 1] }}
            className="relative max-w-6xl mx-auto mt-20"
          >
            <div className="relative z-10 flex flex-col md:flex-row items-center justify-center gap-20 md:gap-0">
              {/* iPhone Frame */}
              <div className="relative rounded-[3rem] md:rounded-[3.5rem] border-[10px] md:border-[12px] border-[#1a1a1a] bg-[#0a0a0a] shadow-[0_80px_150px_-30px_rgba(0,0,0,0.8)] overflow-hidden w-[240px] h-[500px] md:w-[280px] md:h-[580px] -translate-x-[40%] rotate-[-3deg] md:rotate-[-5deg] md:translate-x-[-40px] z-20">
                <img src="https://picsum.photos/seed/app1/600/1200" className="w-full h-full object-cover opacity-60" alt="App UI" />
                <div className="absolute inset-0 bg-gradient-to-t from-black via-transparent to-transparent" />
              </div>

              {/* Tablet Frame */}
              <div className="relative rounded-[2.5rem] md:rounded-[3rem] border-[12px] md:border-[14px] border-[#1a1a1a] bg-[#0a0a0a] shadow-[0_80px_150px_-30px_rgba(0,0,0,0.8)] overflow-hidden w-[320px] md:w-full md:max-w-[550px] aspect-[4/3] translate-x-[30%] rotate-[3deg] md:rotate-[3deg] md:translate-x-[40px] md:-translate-y-10 z-10 scale-110 md:scale-100">
                <img src="https://picsum.photos/seed/tablet/1200/900" className="w-full h-full object-cover opacity-60" alt="Tablet UI" />
                <div className="absolute inset-0 bg-gradient-to-t from-black via-transparent to-transparent" />
              </div>
            </div>
            
            {/* Floating Badges - Repositioned and Responsive */}
            <div className="absolute inset-0 pointer-events-none z-30">
              {/* Brain Badge */}
              <motion.div 
                animate={{ y: [0, -20, 0] }}
                transition={{ duration: 6, repeat: Infinity, ease: "easeInOut" }}
                className="absolute top-[120px] left-[5%] md:top-0 md:left-[5%] md:translate-x-0 glass p-4 lg:p-6 rounded-2xl lg:rounded-3xl border-white/10 shadow-3xl z-40 pointer-events-auto flex"
              >
                <div className="flex items-center gap-3 lg:gap-5">
                  <div className="w-10 h-10 lg:w-12 lg:h-12 rounded-full bg-violet-500/20 flex items-center justify-center flex-shrink-0">
                    <Brain className="text-violet-400 w-5 h-5 lg:w-7 lg:h-7" />
                  </div>
                  <div>
                    <p className="text-[9px] lg:text-[11px] font-black uppercase text-slate-400 tracking-widest">{t.hero.badges.methodology}</p>
                    <p className="text-sm lg:text-lg font-bold">{t.hero.badges.neuroAcoustics}</p>
                  </div>
                </div>
              </motion.div>

              {/* Sleep Quality Badge */}
              <motion.div 
                animate={{ y: [0, 30, 0] }}
                transition={{ duration: 7, repeat: Infinity, ease: "easeInOut", delay: 0.5 }}
                className="absolute top-[400px] right-[5%] md:top-[25%] md:right-[5%] md:left-auto md:translate-x-0 glass p-4 lg:p-6 rounded-2xl lg:rounded-3xl border-white/10 shadow-3xl z-40 pointer-events-auto flex"
              >
                <div className="flex items-center gap-3 lg:gap-5">
                  <div className="w-10 h-10 lg:w-12 lg:h-12 rounded-full bg-emerald-500/20 flex items-center justify-center flex-shrink-0">
                    <CheckCircle2 className="text-emerald-400 w-5 h-5 lg:w-7 lg:h-7" />
                  </div>
                  <div>
                    <p className="text-[9px] lg:text-[11px] font-black uppercase text-slate-400 tracking-widest">{t.hero.badges.sleepQuality}</p>
                    <p className="text-sm lg:text-lg font-bold">{t.hero.badges.improvement}</p>
                  </div>
                </div>
              </motion.div>

              {/* Community Badge - NEW */}
              <motion.div 
                animate={{ y: [0, -15, 0] }}
                transition={{ duration: 8, repeat: Infinity, ease: "easeInOut", delay: 1.5 }}
                className="absolute top-[260px] left-[5%] md:top-1/2 md:-translate-y-1/2 md:left-[2%] md:translate-x-0 glass p-4 lg:p-6 rounded-2xl lg:rounded-3xl border-white/10 shadow-3xl z-40 pointer-events-auto flex"
              >
                <div className="flex items-center gap-3 lg:gap-5">
                  <div className="w-10 h-10 lg:w-12 lg:h-12 rounded-full bg-blue-500/20 flex items-center justify-center flex-shrink-0">
                    <Users className="text-blue-400 w-5 h-5 lg:w-7 lg:h-7" />
                  </div>
                  <div>
                    <p className="text-[9px] lg:text-[11px] font-black uppercase text-slate-400 tracking-widest">{t.hero.badges.community}</p>
                    <p className="text-sm lg:text-lg font-bold">{t.hero.badges.parents}</p>
                  </div>
                </div>
              </motion.div>

              {/* Parent Review Bubble */}
              <motion.div 
                animate={{ y: [0, -15, 0] }}
                transition={{ duration: 5, repeat: Infinity, ease: "easeInOut", delay: 2 }}
                className="absolute top-[540px] left-[5%] md:top-auto md:bottom-0 md:left-[5%] md:translate-x-0 glass p-4 rounded-2xl border-white/10 shadow-2xl z-50 pointer-events-auto max-w-[180px] flex flex-col items-center md:items-start"
              >
                <div className="flex gap-1 mb-2">
                  {[1, 2, 3, 4, 5].map(i => <Star key={i} className="w-3 h-3 fill-amber-400 text-amber-400" />)}
                </div>
                <p className="text-[11px] font-medium text-slate-300 italic text-center md:text-left">{t.hero.review.text}</p>
                <p className="text-[9px] font-bold text-violet-400 mt-1 uppercase tracking-widest">— {t.hero.review.author}</p>
              </motion.div>

              {/* iOS Style Now Playing Widget */}
              <motion.div 
                initial={{ opacity: 1, x: 0 }}
                animate={{ y: [0, 15, 0] }}
                transition={{ duration: 5, repeat: Infinity, ease: "easeInOut", delay: 1 }}
                className="absolute top-0 right-[5%] md:top-auto md:bottom-[25%] md:right-[5%] md:left-auto md:translate-x-0 w-72 lg:w-80 glass p-4 lg:p-5 rounded-[2rem] lg:rounded-[2.5rem] border-white/10 shadow-4xl z-50 pointer-events-auto flex flex-col"
              >
                <div className="flex items-center gap-3 lg:gap-4 mb-3 lg:mb-4">
                  <div className="w-12 h-12 lg:w-16 lg:h-16 rounded-xl lg:rounded-2xl overflow-hidden shadow-lg">
                    <img src="https://picsum.photos/seed/duckling/200/200" alt="Now Playing" className="w-full h-full object-cover" />
                  </div>
                  <div className="flex-1">
                    <p className="text-[9px] lg:text-[10px] font-black uppercase text-violet-400 tracking-widest mb-0.5 lg:mb-1">{t.hero.badges.nowPlaying}</p>
                    <p className="text-xs lg:text-sm font-bold truncate">{t.hero.nowPlaying.title}</p>
                    <p className="text-[9px] lg:text-[10px] text-slate-400">04:20 / 08:30</p>
                  </div>
                  <div className="w-8 h-8 lg:w-10 lg:h-10 rounded-full bg-white/10 flex items-center justify-center">
                    <Pause className="w-3 h-3 lg:w-4 lg:h-4 fill-white" />
                  </div>
                </div>
                <div className="h-1 w-full bg-white/10 rounded-full overflow-hidden">
                  <motion.div 
                    animate={{ width: '50%' }}
                    transition={{ duration: 2, repeat: Infinity, repeatType: 'reverse' }}
                    className="h-full bg-violet-500"
                  />
                </div>
              </motion.div>

              {/* Live Listeners */}
              <motion.div 
                animate={{ y: [0, -10, 0] }}
                transition={{ duration: 4, repeat: Infinity, ease: "easeInOut" }}
                className="absolute -top-10 left-1/2 -translate-x-1/2 glass px-4 lg:px-6 py-2 lg:py-3 rounded-full border-white/10 shadow-2xl z-[60] pointer-events-auto flex items-center gap-2 lg:gap-3 whitespace-nowrap"
              >
                <div className="w-1.5 h-1.5 lg:w-2 lg:h-2 rounded-full bg-red-500 animate-pulse" />
                <p className="text-[9px] lg:text-[10px] font-black uppercase tracking-[0.2em] text-white">
                  <span className="text-red-500">1,240</span> {t.hero.badges.liveListeners}
                </p>
              </motion.div>
            </div>
          </motion.div>
        </div>
      </section>

      <div className="max-w-7xl mx-auto h-px bg-gradient-to-r from-transparent via-white/20 to-transparent" />

      {/* Waitlist Section */}
      <section id="waitlist" className="py-20 px-6 relative overflow-hidden">
        <div className="max-w-4xl mx-auto relative z-10">
          <motion.div 
            initial={{ opacity: 0, y: 30 }}
            whileInView={{ opacity: 1, y: 0 }}
            viewport={{ once: true }}
            className="glass p-12 md:p-20 rounded-[4rem] border-white/10 shadow-4xl text-center relative overflow-hidden"
          >
            {/* Decorative Elements */}
            <div className="absolute top-0 right-0 w-64 h-64 bg-violet-500/10 blur-[100px] rounded-full -translate-y-1/2 translate-x-1/2" />
            <div className="absolute bottom-0 left-0 w-64 h-64 bg-indigo-500/10 blur-[100px] rounded-full translate-y-1/2 -translate-x-1/2" />
            
            <div className="relative z-10">
              <motion.div 
                animate={{ rotate: [0, 10, 0] }}
                transition={{ duration: 5, repeat: Infinity }}
                className="w-20 h-20 rounded-3xl bg-gradient-to-br from-violet-500 to-indigo-600 flex items-center justify-center mx-auto mb-10 shadow-2xl shadow-violet-500/20"
              >
                <Mail className="text-white w-10 h-10" />
              </motion.div>
              
              <h2 className="text-4xl md:text-6xl font-serif font-bold mb-6 tracking-tight">{t.waitlist.title}</h2>
              <p className="text-xl text-slate-400 mb-12 max-w-xl mx-auto leading-relaxed">
                {t.waitlist.subtitle}
              </p>
              
              <form className="flex flex-col md:flex-row gap-4 max-w-2xl mx-auto" onSubmit={(e) => e.preventDefault()}>
                <input 
                  type="email" 
                  placeholder={t.waitlist.placeholder}
                  className="flex-1 px-8 py-5 rounded-2xl bg-white/5 border border-white/10 focus:border-violet-500 focus:outline-none transition-all text-lg"
                />
                <button className="px-10 py-5 bg-white text-black rounded-2xl font-black uppercase tracking-widest hover:bg-violet-500 hover:text-white transition-all shadow-xl">
                  {t.waitlist.button}
                </button>
              </form>
            </div>
          </motion.div>
        </div>
      </section>

      <div className="max-w-7xl mx-auto h-px bg-gradient-to-r from-transparent via-white/20 to-transparent" />

      {/* Psychology Section - Refined Grid with Numbers */}
      <section id="psychology" className="py-40 px-6 relative overflow-hidden">
        <div className="absolute top-1/2 left-0 w-full h-px bg-gradient-to-r from-transparent via-white/10 to-transparent" />
        
        <div className="max-w-7xl mx-auto relative z-10">
          <div className="grid lg:grid-cols-2 gap-24 items-end mb-32">
            <div>
              <h2 className="text-[11px] font-black uppercase tracking-[0.5em] text-violet-500 mb-8">{t.nav.psychology}</h2>
              <h3 className="text-6xl md:text-8xl font-serif font-bold leading-[0.9] tracking-tighter">{t.psychology.title}</h3>
            </div>
            <p className="text-2xl text-slate-400 leading-relaxed font-medium">
              {t.psychology.description}
            </p>
          </div>

          <div className="grid md:grid-cols-3 gap-12">
            {[
              { icon: Brain, title: t.psychology.neuroAcoustics, text: t.psychology.point1, num: '01' },
              { icon: Volume2, title: t.psychology.auditoryIsolation, text: t.psychology.point2, num: '02' },
              { icon: Sparkles, title: t.psychology.melatoninFocus, text: t.psychology.point3, num: '03' }
            ].map((item, i) => (
              <motion.div 
                key={i} 
                initial={{ opacity: 0, y: 20 }}
                whileInView={{ opacity: 1, y: 0 }}
                viewport={{ once: true }}
                transition={{ delay: i * 0.2 }}
                className="relative group"
              >
                <div className="text-[120px] font-serif font-black text-white/5 absolute -top-20 -left-10 select-none group-hover:text-violet-500/10 transition-colors">
                  {item.num}
                </div>
                <div className="relative z-10">
                  <div className="w-16 h-16 rounded-2xl bg-white/5 border border-white/10 flex items-center justify-center mb-10 group-hover:bg-violet-500/20 group-hover:border-violet-500/30 transition-all relative overflow-hidden">
                    <item.icon className="w-8 h-8 text-violet-500 relative z-10" />
                    {i === 0 && (
                      <motion.div 
                        animate={{ x: [-100, 100] }}
                        transition={{ duration: 3, repeat: Infinity, ease: "linear" }}
                        className="absolute inset-0 bg-gradient-to-r from-transparent via-violet-500/20 to-transparent"
                      />
                    )}
                  </div>
                  <h4 className="text-3xl font-bold mb-6 tracking-tight">{item.title}</h4>
                  <p className="text-lg text-slate-400 leading-relaxed">{item.text}</p>
                  
                  {/* Brainwave Animation for first card */}
                  {i === 0 && (
                    <div className="mt-8 flex items-end gap-1 h-8">
                      {[1, 2, 3, 4, 5, 6, 7, 8].map(j => (
                        <motion.div 
                          key={j}
                          animate={{ height: [10, 30, 10] }}
                          transition={{ duration: 1.5, repeat: Infinity, delay: j * 0.1 }}
                          className="w-1 bg-violet-500/30 rounded-full"
                        />
                      ))}
                    </div>
                  )}
                </div>
              </motion.div>
            ))}
          </div>

          {/* Repositioned Widgets - Bottom Center */}
          <div className="mt-48 flex flex-col md:flex-row items-center justify-center gap-8 md:gap-16">
            {/* Brain Sync Badge */}
            <motion.div 
              animate={{ y: [0, -15, 0], rotate: [0, 5, 0] }}
              transition={{ duration: 8, repeat: Infinity, ease: "easeInOut" }}
              className="glass p-4 rounded-2xl border-white/10 shadow-2xl z-40"
            >
              <div className="flex items-center gap-3">
                <div className="w-3 h-3 rounded-full bg-violet-500 animate-pulse" />
                <p className="text-[10px] font-black uppercase tracking-widest text-slate-400">{t.psychology.brainSync}</p>
              </div>
            </motion.div>

            {/* Clinical Study Badge */}
            <motion.div 
              animate={{ y: [0, 15, 0] }}
              transition={{ duration: 6, repeat: Infinity, ease: "easeInOut", delay: 1 }}
              className="glass p-5 rounded-3xl border-white/10 shadow-3xl z-50 flex items-center gap-4"
            >
              <div className="flex items-center gap-4">
                <div className="w-10 h-10 rounded-xl bg-indigo-500/20 flex items-center justify-center">
                  <CheckCircle2 className="text-indigo-400 w-6 h-6" />
                </div>
                <div>
                  <p className="text-[9px] font-black uppercase text-slate-500 tracking-widest">{t.psychology.clinicalStudy}</p>
                  <p className="text-sm font-bold">{t.psychology.successRate}</p>
                </div>
              </div>
            </motion.div>
          </div>
        </div>
      </section>

      <div className="max-w-7xl mx-auto h-px bg-gradient-to-r from-transparent via-white/20 to-transparent" />

      {/* Ritual Section - New Cool Element */}
      <section className="py-40 px-6 bg-[#050505] relative overflow-hidden">
        <div className="max-w-7xl mx-auto relative">
          {/* Floating Element in Ritual - Repositioned for better visibility */}
          <motion.div 
            animate={{ y: [0, 20, 0], rotate: [-5, 5, -5] }}
            transition={{ duration: 8, repeat: Infinity, ease: "easeInOut" }}
            className="absolute -top-20 left-1/2 -translate-x-1/2 md:-left-10 md:top-0 md:translate-x-0 glass p-6 rounded-full border-white/10 shadow-4xl z-50 flex flex-col items-center gap-2"
          >
            <div className="flex flex-col items-center gap-2">
              <div className="w-10 h-10 md:w-12 md:h-12 rounded-full bg-amber-500/20 flex items-center justify-center">
                <Sparkles className="text-amber-400 w-5 h-5 md:w-6 md:h-6" />
              </div>
              <p className="text-[9px] font-black uppercase tracking-widest text-amber-400">{t.ritual.autoOff}</p>
            </div>
          </motion.div>

          <div className="text-center mb-24">
            <h2 className="text-[11px] font-black uppercase tracking-[0.5em] text-violet-500 mb-8">{t.ritual.title}</h2>
            <h3 className="text-6xl md:text-8xl font-serif font-bold leading-[0.9] tracking-tighter mb-10">{t.ritual.subtitle}</h3>
          </div>

          <div className="grid md:grid-cols-3 gap-8">
            {[
              { step: '01', title: t.ritual.step1.title, text: t.ritual.step1.text, icon: Sparkles },
              { step: '02', title: t.ritual.step2.title, text: t.ritual.step2.text, icon: Volume2 },
              { step: '03', title: t.ritual.step3.title, text: t.ritual.step3.text, icon: Heart }
            ].map((item, i) => (
              <div key={i} className="glass p-10 rounded-[3rem] border-white/5 relative group hover:bg-white/5 transition-all">
                <div className="text-6xl font-serif font-black text-white/5 absolute top-10 right-10">{item.step}</div>
                <item.icon className="w-12 h-12 text-violet-500 mb-8 group-hover:scale-110 transition-transform" />
                <h4 className="text-3xl font-bold mb-4">{item.title}</h4>
                <p className="text-slate-400 text-lg">{item.text}</p>
              </div>
            ))}
          </div>
        </div>
      </section>

      <div className="max-w-7xl mx-auto h-px bg-gradient-to-r from-transparent via-white/20 to-transparent" />

      {/* Effects Section - Hardware Mixer Style */}
      <section id="effects" className="py-40 px-6 bg-[#080808]">
        <div className="max-w-7xl mx-auto">
          <div className="grid lg:grid-cols-2 gap-20 items-center">
            <div>
              <h2 className="text-5xl md:text-7xl font-serif font-bold mb-10 leading-[0.9]">{t.effects.title}</h2>
              <p className="text-xl text-slate-400 mb-12 leading-relaxed">
                {t.effects.description}
              </p>
                <div className="space-y-8">
                {effects.map((effect) => {
                  const isActive = activeEffects.includes(effect.id);
                  return (
                    <div key={effect.id} className="space-y-3 group">
                      <div className="flex justify-between items-center">
                        <div className="flex items-center gap-3">
                          <div className={`w-2 h-2 rounded-full transition-all duration-500 ${isActive ? 'bg-violet-500 shadow-[0_0_10px_rgba(139,92,246,1)]' : 'bg-white/10'}`} />
                          <effect.icon className={`w-5 h-5 transition-colors duration-500 ${isActive ? effect.color : 'text-slate-600'}`} />
                          <span className={`text-sm font-bold uppercase tracking-widest transition-colors duration-500 ${isActive ? 'text-white' : 'text-slate-500'}`}>{effect.label[lang]}</span>
                        </div>
                        <span className="text-xs font-mono text-slate-500">{isActive ? '85%' : '0%'}</span>
                      </div>
                      <div 
                        onClick={() => toggleEffect(effect.id)}
                        className="h-3 w-full bg-white/5 rounded-full cursor-pointer relative overflow-hidden border border-white/5 p-0.5"
                      >
                        <motion.div 
                          initial={false}
                          animate={{ 
                            width: isActive ? '85%' : '0%',
                            backgroundColor: isActive ? '#8b5cf6' : '#1e1b4b'
                          }}
                          className="h-full rounded-full shadow-[0_0_20px_rgba(139,92,246,0.3)]"
                        />
                      </div>
                    </div>
                  );
                })}
              </div>
            </div>

            <div className="relative">
              {/* Floating Sound Waves in Effects */}
              <motion.div 
                animate={{ scale: [1, 1.1, 1] }}
                transition={{ duration: 3, repeat: Infinity, ease: "easeInOut" }}
                className="absolute -top-10 -right-10 w-32 h-32 bg-violet-500/10 blur-3xl rounded-full z-0"
              />
              
              {/* New Floating Element: Spatial Audio */}
              <motion.div 
                animate={{ y: [0, -20, 0] }}
                transition={{ duration: 5, repeat: Infinity, ease: "easeInOut" }}
                className="absolute -top-20 left-1/2 -translate-x-1/2 glass px-6 py-3 rounded-2xl border-white/10 shadow-3xl z-50 flex items-center gap-3"
              >
                <Volume2 className="w-4 h-4 text-violet-400" />
                <p className="text-[10px] font-black uppercase tracking-[0.3em] text-white">{t.effects.spatialAudio}</p>
              </motion.div>

              <div className="aspect-square rounded-[3rem] glass border-white/5 p-12 flex items-center justify-center relative overflow-hidden z-10">
                <div className="absolute inset-0 bg-gradient-to-br from-violet-500/10 to-transparent" />
                <motion.div 
                  animate={{ rotate: 360 }}
                  transition={{ duration: 20, repeat: Infinity, ease: "linear" }}
                  className="w-full h-full border-2 border-dashed border-white/10 rounded-full flex items-center justify-center"
                >
                  <div className="w-3/4 h-3/4 border-2 border-dashed border-white/20 rounded-full flex items-center justify-center">
                    <div className="w-1/2 h-1/2 border-2 border-dashed border-white/30 rounded-full" />
                  </div>
                </motion.div>
                
                <div className="absolute inset-0 flex items-center justify-center">
                  <div className="w-32 h-32 rounded-full bg-violet-600 flex items-center justify-center shadow-[0_0_50px_rgba(139,92,246,0.5)]">
                    <Volume2 className="w-12 h-12 text-white" />
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </section>

      <div className="max-w-7xl mx-auto h-px bg-gradient-to-r from-transparent via-white/20 to-transparent" />

      {/* Library Section - Editorial Style */}
      <section id="stories" className="py-40 px-6">
        <div className="max-w-7xl mx-auto">
          <div className="flex flex-col md:flex-row justify-between items-end gap-10 mb-24 relative">
            <div className="max-w-2xl">
              <h2 className="text-11px font-black uppercase tracking-[0.4em] text-violet-500 mb-6">{t.nav.stories}</h2>
              <h3 className="text-5xl md:text-7xl font-serif font-bold leading-[0.9]">{t.stories.title}</h3>
            </div>
            
            {/* New Floating Element: Safe for Kids */}
            <motion.div 
              animate={{ y: [0, -15, 0] }}
              transition={{ duration: 4, repeat: Infinity, ease: "easeInOut" }}
              className="absolute -top-10 right-0 glass px-6 py-3 rounded-2xl border-white/10 shadow-3xl z-50 hidden md:flex items-center gap-3"
            >
              <div className="w-8 h-8 rounded-lg bg-emerald-500/20 flex items-center justify-center">
                <Heart className="text-emerald-400 w-5 h-5" />
              </div>
              <p className="text-[10px] font-black uppercase tracking-widest text-white">{t.stories.safeForKids}</p>
            </motion.div>

            <div className="flex gap-4">
              <button className="px-8 py-3 rounded-full glass border-white/10 text-xs font-black uppercase tracking-widest hover:bg-white hover:text-black transition-all">
                {t.stories.viewAll}
              </button>
            </div>
          </div>

          <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-12">
            {stories.map((story) => (
              <motion.div 
                key={story.id}
                initial={{ opacity: 0, y: 20 }}
                whileInView={{ opacity: 1, y: 0 }}
                viewport={{ once: true }}
                className="group"
              >
                <div className="relative aspect-[4/5] rounded-[2.5rem] overflow-hidden mb-8 border border-white/5">
                  <img src={story.image} alt={story.title[lang]} className="w-full h-full object-cover grayscale group-hover:grayscale-0 transition-all duration-700" referrerPolicy="no-referrer" />
                  <div className="absolute inset-0 bg-gradient-to-t from-black via-transparent to-transparent opacity-60" />
                  
                  {story.status === 'comingSoon' && (
                    <div className="absolute top-8 right-8 px-4 py-1.5 rounded-full bg-amber-500 text-black text-[10px] font-black uppercase tracking-widest">
                      {t.stories.comingSoon}
                    </div>
                  )}

                  {story.id === 1 && (
                    <div className="absolute top-8 left-8 glass px-4 py-1.5 rounded-full border-white/20 text-white text-[10px] font-black uppercase tracking-widest z-20">
                      🔥 {t.stories.popular}
                    </div>
                  )}

                  <div className="absolute inset-0 flex items-center justify-center opacity-0 group-hover:opacity-100 transition-opacity">
                    <div className="w-20 h-20 rounded-full bg-white text-black flex items-center justify-center scale-75 group-hover:scale-100 transition-transform">
                      <Play className="fill-black w-8 h-8 ml-1" />
                    </div>
                  </div>
                </div>
                
                <div className="flex justify-between items-start px-2">
                  <div>
                    <h4 className="text-3xl font-serif font-bold mb-2">{story.title[lang]}</h4>
                    <p className="text-sm font-bold uppercase tracking-widest text-slate-500">{story.duration} MIN • {story.status === 'published' ? t.stories.published : t.stories.comingSoon}</p>
                  </div>
                  <ArrowRight className="w-6 h-6 text-white/20 group-hover:text-violet-500 group-hover:translate-x-2 transition-all" />
                </div>
              </motion.div>
            ))}
          </div>
        </div>
      </section>

      <div className="max-w-7xl mx-auto h-px bg-gradient-to-r from-transparent via-white/20 to-transparent mb-20" />

      {/* Dedication Section - Luxury Split with Parents Image */}
      <section className="py-40 px-6 bg-white text-black rounded-[5rem] mx-4 mb-4 relative overflow-hidden">
        <div className="max-w-7xl mx-auto grid lg:grid-cols-2 gap-32 items-center">
          <motion.div
            initial={{ opacity: 0, x: -30 }}
            whileInView={{ opacity: 1, x: 0 }}
            viewport={{ once: true }}
          >
            <h2 className="text-[11px] font-black uppercase tracking-[0.5em] text-violet-600 mb-10">{t.dedication.mission}</h2>
            <h3 className="text-6xl md:text-8xl font-serif font-bold mb-12 leading-[0.85] tracking-tighter">{t.dedication.title}</h3>
            <p className="text-2xl text-slate-600 leading-relaxed mb-16 font-medium">
              {t.dedication.description}
            </p>
            <div className="grid sm:grid-cols-2 gap-10">
              {[
                { title: t.dedication.feature1.title, text: t.dedication.feature1.text },
                { title: t.dedication.feature2.title, text: t.dedication.feature2.text },
                { title: t.dedication.feature3.title, text: t.dedication.feature3.text },
                { title: t.dedication.feature4.title, text: t.dedication.feature4.text }
              ].map((item, i) => (
                <div key={i} className="space-y-4">
                  <div className="w-14 h-14 rounded-2xl bg-violet-100 flex items-center justify-center">
                    <CheckCircle2 className="text-violet-600 w-7 h-7" />
                  </div>
                  <h4 className="text-2xl font-bold tracking-tight">{item.title}</h4>
                  <p className="text-slate-500 leading-relaxed">{item.text}</p>
                </div>
              ))}
            </div>
          </motion.div>
          <motion.div 
            initial={{ opacity: 0, scale: 0.9 }}
            whileInView={{ opacity: 1, scale: 1 }}
            viewport={{ once: true }}
            className="relative"
          >
            <div className="relative rounded-[4rem] overflow-hidden shadow-[0_50px_100px_-20px_rgba(0,0,0,0.2)]">
              <img 
                src="https://picsum.photos/seed/parents-reading/1200/1600" 
                alt="Parents reading to child" 
                className="w-full h-full object-cover hover:scale-105 transition-transform duration-1000"
                referrerPolicy="no-referrer"
              />
              <div className="absolute inset-0 bg-gradient-to-t from-black/40 to-transparent" />
            </div>
            
            <div className="absolute -bottom-10 -right-10 glass p-10 rounded-[3rem] border-white/20 shadow-4xl max-w-[280px] rotate-[5deg] z-50">
              <div className="flex gap-2 mb-4">
                {[1, 2, 3, 4, 5].map(i => <Star key={i} className="w-5 h-5 fill-amber-400 text-amber-400" />)}
              </div>
              <p className="text-lg font-serif italic text-slate-800 mb-4">{t.dedication.quote}</p>
              <p className="text-xs font-black uppercase tracking-widest text-violet-600">— {t.dedication.author}</p>
            </div>

            {/* New Floating Element: Pediatrician Verified */}
            <motion.div 
              animate={{ y: [0, -30, 0], rotate: [0, -5, 0] }}
              transition={{ duration: 6, repeat: Infinity, ease: "easeInOut" }}
              className="absolute -top-10 -left-10 glass p-8 rounded-[2.5rem] border-white/20 shadow-4xl z-50 hidden md:block"
            >
              <div className="flex items-center gap-4">
                <div className="w-12 h-12 rounded-2xl bg-emerald-500 flex items-center justify-center shadow-lg shadow-emerald-500/20">
                  <CheckCircle2 className="text-white w-7 h-7" />
                </div>
                <div>
                  <p className="text-[10px] font-black uppercase text-slate-500 tracking-widest mb-1">{t.dedication.verifiedBy}</p>
                  <p className="text-lg font-bold text-slate-800">{t.dedication.pediatricians}</p>
                </div>
              </div>
            </motion.div>
          </motion.div>
        </div>
      </section>

      <div className="max-w-7xl mx-auto h-px bg-gradient-to-r from-transparent via-white/20 to-transparent" />

      {/* Footer */}
      <footer className="py-32 px-6">
        <div className="max-w-7xl mx-auto">
          <div className="grid md:grid-cols-4 gap-16 mb-24">
            <div className="col-span-2">
              <div className="flex items-center gap-3 mb-8">
                <div className="w-10 h-10 rounded-xl bg-violet-600 flex items-center justify-center">
                  <Sparkles className="text-white w-6 h-6" />
                </div>
                <span className="text-3xl font-bold tracking-tighter">snovi<span className="text-violet-500">.fm</span></span>
              </div>
              <p className="text-xl text-slate-500 max-w-sm leading-relaxed">
                {t.footer.tagline}
              </p>
            </div>
            <div>
              <h5 className="text-xs font-black uppercase tracking-widest text-white mb-8">{t.footer.navigation}</h5>
              <ul className="space-y-4 text-slate-500 font-bold text-sm">
                <li><a href="#psychology" className="hover:text-violet-500 transition-colors">{t.nav.psychology}</a></li>
                <li><a href="#effects" className="hover:text-violet-500 transition-colors">{t.nav.effects}</a></li>
                <li><a href="#stories" className="hover:text-violet-500 transition-colors">{t.nav.stories}</a></li>
              </ul>
            </div>
            <div>
              <h5 className="text-xs font-black uppercase tracking-widest text-white mb-8">{t.footer.social}</h5>
              <ul className="space-y-4 text-slate-500 font-bold text-sm">
                <li><a href="#" className="hover:text-violet-500 transition-colors">Instagram</a></li>
                <li><a href="#" className="hover:text-violet-500 transition-colors">TikTok</a></li>
                <li><a href="#" className="hover:text-violet-500 transition-colors">Facebook</a></li>
              </ul>
            </div>
          </div>
          
          <div className="pt-12 border-t border-white/5 flex flex-col md:flex-row justify-between items-center gap-8">
            <p className="text-xs font-bold text-slate-600 uppercase tracking-[0.3em]">
              © 2026 snovi.fm • {t.footer.rights}
            </p>
            <div className="flex gap-8 text-[10px] font-black uppercase tracking-widest text-slate-600">
              <a href="#" className="hover:text-white transition-colors">{t.footer.privacy}</a>
              <a href="#" className="hover:text-white transition-colors">{t.footer.terms}</a>
              <a href="#" className="hover:text-white transition-colors">{t.footer.cookies}</a>
            </div>
          </div>
        </div>
      </footer>
    </div>
  );
}
