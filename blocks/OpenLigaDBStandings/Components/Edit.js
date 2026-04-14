import {
	InspectorControls,
	useBlockProps,
	RichText,
} from '@wordpress/block-editor';
import { Panel, PanelBody, ToggleControl } from '@wordpress/components';
import { default as ServerSideRender } from '@wordpress/server-side-render';
import { useEffect, useState } from '@wordpress/element';
import { LeagueSelectControl } from '../../Components/LeagueSelectControl';
import { TeamSelectControl } from '../../Components/TeamSelectControl';

const Edit = ( props ) => {
	const { setAttributes, attributes } = props;

	const blockProps = useBlockProps();
	const [ leagueName, setLeagueName ] = useState( '' );
	const [ leagueSelected, setLeagueSelected ] = useState(
		!! attributes.leagueShortcut
	);

	useEffect( () => {
		setLeagueSelected( !! attributes.leagueShortcut );
	}, [ attributes.leagueShortcut ] );

	return (
		<div { ...blockProps }>
			<InspectorControls key={ 'openligadb-standings-ic' }>
				<Panel key={ 'openligadb-standings-ic-panel' }>
					<PanelBody key={ 'openligadb-standings-ic-panel-body' }>
						<ToggleControl
							label="Titel anzeigen"
							checked={ attributes.showTitle }
							onChange={ ( showTitle ) =>
								setAttributes( { showTitle } )
							}
						/>
						<LeagueSelectControl
							leagueShortcut={ attributes.leagueShortcut }
							leagueSeason={ attributes.leagueSeason }
							autoSelect={ true }
							onChange={ (
								leagueShortcut,
								leagueSeason,
								name
							) => {
								setLeagueName( name || '' );
								const newAttributes = {
									leagueShortcut,
									leagueSeason: parseInt( leagueSeason ),
									highlightTeamId: 0,
								};
								if ( ! attributes.title && name ) {
									newAttributes.title = name;
								}
								setAttributes( newAttributes );
							} }
						/>
						{ leagueSelected && (
							<TeamSelectControl
								label="Hervorgehobene Mannschaft:"
								leagueShortcut={ attributes.leagueShortcut }
								leagueSeason={ attributes.leagueSeason }
								teamId={ attributes.highlightTeamId }
								onChange={ ( value ) =>
									setAttributes( {
										highlightTeamId: parseInt( value ),
									} )
								}
							/>
						) }
					</PanelBody>
				</Panel>
			</InspectorControls>

			{ attributes.showTitle && (
				<RichText
					tagName="h2"
					value={ attributes.title }
					onChange={ ( title ) => setAttributes( { title } ) }
					placeholder={ leagueName || 'Tabelle' }
				/>
			) }

			<ServerSideRender
				key={ 'openligadb-standings-ssr' }
				block={ props.name }
				attributes={ props.attributes }
			/>
		</div>
	);
};

export default Edit;
