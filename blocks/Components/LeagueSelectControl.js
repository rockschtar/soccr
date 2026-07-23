import { useCallback, useEffect, useState } from '@wordpress/element';
import { ComboboxControl, Disabled } from '@wordpress/components';
import PropTypes from 'prop-types';
import { __ } from '@wordpress/i18n';
import apiFetch from '@wordpress/api-fetch';

export const LeagueSelectControl = ( props ) => {
	const { onChange, autoSelect } = props;
	const [ leagues, setLeagues ] = useState( [] );
	const [ leagueShortcutSeason, setLeagueShortcutSeason ] = useState(
		props.leagueShortcut
			? props.leagueShortcut + '###' + props.leagueSeason
			: null
	);

	useEffect( () => {
		let cancelled = false;

		let path = '/openligadb/v1/leagues';

		if ( props.leagueShortcut && props.leagueSeason ) {
			path += `?includeShortcut=${ props.leagueShortcut }&includeSeason=${ props.leagueSeason }`;
		}

		apiFetch( { path } )
			.then( ( data ) => {
				if ( cancelled ) {
					return;
				}

				const leagueOptions = data.map( ( league ) => ( {
					value: league.leagueShortcut + '###' + league.leagueSeason,
					label: league.leagueNameShort,
				} ) );

				setLeagues( leagueOptions );
			} )
			.catch( () => {} );

		return () => {
			cancelled = true;
		};
	}, [ props.leagueShortcut, props.leagueSeason ] );

	const onLeagueChange = useCallback(
		( value ) => {
			if ( ! value ) {
				return;
			}

			const [ leagueShortcut, leagueSeason ] = value.split( '###' );
			const selectedLeague = leagues.find( ( l ) => l.value === value );
			const leagueName = selectedLeague ? selectedLeague.label : '';
			onChange( leagueShortcut, leagueSeason, leagueName );
			setLeagueShortcutSeason( value );
		},
		[ leagues, onChange ]
	);

	useEffect( () => {
		if ( leagues.length === 0 ) {
			return;
		}

		const valueExistsInOptions = leagues.some(
			( l ) => l.value === leagueShortcutSeason
		);

		if ( ! valueExistsInOptions ) {
			if ( autoSelect ) {
				onLeagueChange( leagues[ 0 ].value );
			} else {
				setLeagueShortcutSeason( null );
			}
		}
	}, [ leagues, leagueShortcutSeason, autoSelect, onLeagueChange ] );

	return (
		<Disabled isDisabled={ props.disabled ?? false }>
			<ComboboxControl
				label={ __( 'League:', 'soccr' ) }
				value={ leagueShortcutSeason }
				onChange={ onLeagueChange }
				options={ leagues }
			/>
		</Disabled>
	);
};

LeagueSelectControl.propTypes = {
	leagueShortcut: PropTypes.string,
	leagueSeason: PropTypes.number,
	onChange: PropTypes.func,
	disabled: PropTypes.bool,
	autoSelect: PropTypes.bool,
};
