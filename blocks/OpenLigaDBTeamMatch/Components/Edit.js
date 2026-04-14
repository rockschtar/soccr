import { default as ServerSideRender } from '@wordpress/server-side-render';
import {
	SelectControl,
	ToggleControl,
	Panel,
	PanelBody,
} from '@wordpress/components';
import {
	InspectorControls,
	useBlockProps,
	RichText,
} from '@wordpress/block-editor';
import { __ } from '@wordpress/i18n';
import { LeagueSelectControl } from '../../Components/LeagueSelectControl';
import { TeamSelectControl } from '../../Components/TeamSelectControl';

const displayModeLabels = {
	current: __( 'Aktuelles Spiel', 'soccr' ),
	next: __( 'Nächstes Spiel', 'soccr' ),
	last: __( 'Letztes Spiel', 'soccr' ),
};

const Edit = ( props ) => {
	const { setAttributes, attributes } = props;

	const blockProps = useBlockProps();

	const onLeagueChange = ( leagueShortcut, leagueSeason ) => {
		setAttributes( {
			leagueShortcut,
			leagueSeason: parseInt( leagueSeason ),
			teamId: 0,
		} );
	};

	const onTeamChange = ( teamId ) => {
		setAttributes( { teamId: parseInt( teamId ) } );
	};

	const displayModeOptions = [
		{ value: 'current', label: displayModeLabels.current },
		{ value: 'next', label: displayModeLabels.next },
		{ value: 'last', label: displayModeLabels.last },
	];

	return (
		<div { ...blockProps }>
			<InspectorControls key={ 'openligadb-team-match-ic' }>
				<Panel key={ 'openligadb-team-match-ic-panel' }>
					<PanelBody key={ 'openligadb-team-match-ic-panel-body' }>
						<ToggleControl
							label={ __( 'Titel anzeigen', 'soccr' ) }
							checked={ attributes.showTitle }
							onChange={ ( showTitle ) =>
								setAttributes( { showTitle } )
							}
						/>
						<ToggleControl
							label={ __( 'Team-Icons anzeigen', 'soccr' ) }
							checked={ attributes.showTeamIcons }
							onChange={ ( showTeamIcons ) =>
								setAttributes( { showTeamIcons } )
							}
						/>
						<LeagueSelectControl
							key={ 'openligadb-team-match-league-select' }
							leagueShortcut={ attributes.leagueShortcut }
							leagueSeason={ attributes.leagueSeason }
							onChange={ onLeagueChange }
						/>
						{ attributes.leagueShortcut && (
							<TeamSelectControl
								key={ 'openligadb-team-match-team-select' }
								leagueShortcut={ attributes.leagueShortcut }
								leagueSeason={ attributes.leagueSeason }
								teamId={ attributes.teamId }
								onChange={ onTeamChange }
							/>
						) }
						<SelectControl
							key={ 'openligadb-team-match-display-mode' }
							label={ __( 'Anzeige:', 'soccr' ) }
							value={ attributes.displayMode }
							options={ displayModeOptions }
							onChange={ ( displayMode ) => {
								setAttributes( { displayMode } );
							} }
						/>
					</PanelBody>
				</Panel>
			</InspectorControls>

			{ attributes.showTitle && (
				<RichText
					tagName="h4"
					value={ attributes.title }
					onChange={ ( title ) => setAttributes( { title } ) }
					placeholder={ displayModeLabels[ attributes.displayMode ] }
				/>
			) }

			<ServerSideRender
				key={ 'openligadb-team-match-ssr' }
				block={ props.name }
				attributes={ props.attributes }
			/>
		</div>
	);
};

export default Edit;
