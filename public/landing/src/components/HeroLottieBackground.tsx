import { useEffect, useRef } from 'react';
import lottie from 'lottie-web';

type HeroLottieBackgroundProps = {
  className?: string;
};

export function HeroLottieBackground({ className = '' }: HeroLottieBackgroundProps) {
  const containerRef = useRef<HTMLDivElement | null>(null);

  useEffect(() => {
    if (!containerRef.current) {
      return;
    }

    const animationPath = new URL('hero-bg.json', document.baseURI).toString();

    const animation = lottie.loadAnimation({
      container: containerRef.current,
      renderer: 'svg',
      loop: true,
      autoplay: true,
      path: animationPath,
      rendererSettings: {
        preserveAspectRatio: 'xMidYMid slice',
        progressiveLoad: true,
      },
    });

    return () => {
      animation.destroy();
    };
  }, []);

  return <div ref={containerRef} aria-hidden="true" className={className} />;
}
