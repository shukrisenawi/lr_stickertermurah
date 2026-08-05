import type { ImgHTMLAttributes } from 'react';

interface ResponsiveDesignImageProps extends Omit<ImgHTMLAttributes<HTMLImageElement>, 'src' | 'alt'> {
  src: string;
  mobileSrc?: string | null;
  alt: string;
}

export default function ResponsiveDesignImage({ src, mobileSrc, alt, ...props }: ResponsiveDesignImageProps) {
  return (
    <picture className="block">
      {mobileSrc && mobileSrc !== src && <source media="(max-width: 767px)" srcSet={mobileSrc} />}
      <img src={src} alt={alt} {...props} />
    </picture>
  );
}
