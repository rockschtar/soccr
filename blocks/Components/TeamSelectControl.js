import { useEffect, useState, useRef } from '@wordpress/element';
import { SelectControl } from '@wordpress/components';
import PropTypes from 'prop-types';
import { __ } from '@wordpress/i18n';
import apiFetch from '@wordpress/api-fetch';

export const TeamSelectControl = ( props ) => {
	const {
		leagueShortcut,
		leagueSeason,
		label = __( 'Team:', 'soccr' ),
	} = props;
	const [ teams, setTeams ] = useState( [] );
	const [ teamId, setTeamId ] = useState( props.teamId );

	useEffect( () => {
		setTeamId( props.teamId );
	}, [ props.teamId ] );
	const componentMounted = useRef( true );

	useEffect( () => {
		componentMounted.current = true;

		apiFetch( {
			path:
				'/openligadb/v1/teams?leagueShortcut=' +
				leagueShortcut +
				'&leagueSeason=' +
				leagueSeason,
		} ).then( ( teamData ) => {
			const teamOptions = teamData.map( ( league ) => {
				return {
					value: league.teamId,
					label: league.teamName,
				};
			} );

			teamOptions.unshift( {
				value: 0,
				label: __( 'Select team', 'soccr' ),
			} );

			if ( componentMounted.current ) {
				setTeams( teamOptions );
			}
		} );

		return () => {
			componentMounted.current = false;
		};
	}, [ leagueShortcut, leagueSeason ] );

	const onTeamChange = ( value ) => {
		props.onChange( value );
		setTeamId( value );
	};

	return (
		<SelectControl
			label={ label }
			value={ teamId }
			onChange={ onTeamChange }
			options={ teams }
		/>
	);
};

TeamSelectControl.propTypes = {
	leagueShortcut: PropTypes.string,
	leagueSeason: PropTypes.number,
	label: PropTypes.string,
	onChange: PropTypes.func,
};
