import {
	getBlockType,
	getCategories,
	registerBlockType,
	setCategories,
	unregisterBlockType,
} from '@wordpress/blocks';
import groupMatchesMetadata from '../OpenLigaDBGroupMatches/block.json';
import standingsMetadata from '../OpenLigaDBStandings/block.json';
import teamMatchMetadata from '../OpenLigaDBTeamMatch/block.json';

beforeAll( () => {
	setCategories( [ ...getCategories(), { slug: 'soccr', title: 'Soccr' } ] );
} );

const noop = () => null;

describe( 'Block registration', () => {
	afterEach( () => {
		[
			'soccr/standings',
			'soccr/group-matches',
			'soccr/team-match',
		].forEach( ( name ) => {
			if ( getBlockType( name ) ) {
				unregisterBlockType( name );
			}
		} );
	} );

	describe( 'soccr/standings', () => {
		beforeEach( () => {
			registerBlockType( standingsMetadata.name, {
				...standingsMetadata,
				edit: noop,
				save: noop,
			} );
		} );

		it( 'registers the block', () => {
			expect( getBlockType( 'soccr/standings' ) ).toBeDefined();
		} );

		it( 'has correct default attributes', () => {
			const { attributes } = getBlockType( 'soccr/standings' );
			expect( attributes.leagueShortcut.default ).toBe( 'bl2' );
			expect( attributes.showTitle.default ).toBe( true );
			expect( attributes.align.default ).toBe( 'center' );
		} );
	} );

	describe( 'soccr/group-matches', () => {
		beforeEach( () => {
			registerBlockType( groupMatchesMetadata.name, {
				...groupMatchesMetadata,
				edit: noop,
				save: noop,
			} );
		} );

		it( 'registers the block', () => {
			expect( getBlockType( 'soccr/group-matches' ) ).toBeDefined();
		} );

		it( 'has correct default attributes', () => {
			const { attributes } = getBlockType( 'soccr/group-matches' );
			expect( attributes.defaultCurrentGroup.default ).toBe( true );
			expect( attributes.pagination.default ).toBe( true );
		} );
	} );

	describe( 'soccr/team-match', () => {
		beforeEach( () => {
			registerBlockType( teamMatchMetadata.name, {
				...teamMatchMetadata,
				edit: noop,
				save: noop,
			} );
		} );

		it( 'registers the block', () => {
			expect( getBlockType( 'soccr/team-match' ) ).toBeDefined();
		} );

		it( 'has correct default attributes', () => {
			const { attributes } = getBlockType( 'soccr/team-match' );
			expect( attributes.displayMode.default ).toBe( 'current' );
			expect( attributes.teamId.default ).toBe( 0 );
			expect( attributes.showTeamIcons.default ).toBe( true );
		} );

		it( 'restricts displayMode to valid values', () => {
			const { attributes } = getBlockType( 'soccr/team-match' );
			expect( attributes.displayMode.enum ).toEqual( [
				'current',
				'next',
				'last',
			] );
		} );
	} );
} );
