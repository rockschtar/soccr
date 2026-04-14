import { SVG, Path, Rect } from '@wordpress/primitives';

export const standingsIcon = (
	<SVG xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24">
		<Rect x="3" y="4" width="18" height="2" rx="0.5" />
		<Rect x="3" y="8" width="14" height="2" rx="0.5" />
		<Rect x="3" y="12" width="11" height="2" rx="0.5" />
		<Rect x="3" y="16" width="8" height="2" rx="0.5" />
	</SVG>
);

export const groupMatchesIcon = (
	<SVG xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24">
		<Path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm0 18c-4.41 0-8-3.59-8-8s3.59-8 8-8 8 3.59 8 8-3.59 8-8 8z" />
		<Path d="M12 5.5l1.3 3.1 3.3.3-2.5 2.2.7 3.3L12 12.6l-2.8 1.8.7-3.3-2.5-2.2 3.3-.3z" />
	</SVG>
);

export const teamMatchIcon = (
	<SVG xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24">
		<Path d="M7.5 7a2.5 2.5 0 100-5 2.5 2.5 0 000 5zM16.5 7a2.5 2.5 0 100-5 2.5 2.5 0 000 5z" />
		<Path d="M4 9.5C4 8.67 4.67 8 5.5 8h4c.83 0 1.5.67 1.5 1.5V14H4V9.5zM13 9.5c0-.83.67-1.5 1.5-1.5h4c.83 0 1.5.67 1.5 1.5V14h-7V9.5z" />
		<Path
			d="M10.5 16.5L12 15l1.5 1.5"
			fill="none"
			stroke="currentColor"
			strokeWidth="1.5"
		/>
		<Rect x="3" y="17" width="7" height="2" rx="0.5" />
		<Rect x="14" y="17" width="7" height="2" rx="0.5" />
	</SVG>
);
