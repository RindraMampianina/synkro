import { Orbit } from 'lucide-react';

type BrandMarkProps = {
  size?: 'sm' | 'md' | 'lg';
  light?: boolean;
};

const boxSizes = {
  sm: 'h-7 w-7',
  md: 'h-9 w-9',
  lg: 'h-11 w-11',
};

const iconSizes = {
  sm: 'h-3.5 w-3.5',
  md: 'h-4 w-4',
  lg: 'h-5 w-5',
};

export default function BrandMark({ size = 'md', light = false }: BrandMarkProps) {
  return (
    <span
      className={`inline-flex ${boxSizes[size]} items-center justify-center rounded-lg ${
        light ? 'bg-white/15 text-white' : 'bg-accent-700 text-white'
      }`}
      aria-hidden
    >
      <Orbit className={iconSizes[size]} strokeWidth={2.2} />
    </span>
  );
}
