import { default as ServerSideRender } from '@wordpress/server-side-render';
import {
	TextControl,
	CheckboxControl,
	ToggleControl,
	Panel,
	PanelBody,
} from '@wordpress/components';
import { InspectorControls, useBlockProps } from '@wordpress/block-editor';
import { useEffect } from '@wordpress/element';
import { __ } from '@wordpress/i18n';
import { LeagueSelectControl } from '../../Components/LeagueSelectControl';

const Edit = ( props ) => {
	const { setAttributes, attributes, clientId } = props;

	const { blockId } = attributes;

	const blockProps = useBlockProps();

	useEffect( () => {
		if ( ! blockId ) {
			setAttributes( {
				blockId: clientId,
			} );
		}
	}, [ clientId ] );

	return (
		<div { ...blockProps }>
			<InspectorControls key={ 'openligadb-group-matches-ic' }>
				<Panel key={ 'openligadb-group-matches-ic-panel' }>
					<PanelBody key={ 'openligadb-group-matches-ic-panel-body' }>
						<ToggleControl
							label={ __( 'Titel anzeigen', 'soccr' ) }
							checked={ attributes.showTitle }
							onChange={ ( showTitle ) =>
								setAttributes( { showTitle } )
							}
						/>
						<LeagueSelectControl
							leagueShortcut={ attributes.leagueShortcut }
							leagueSeason={ attributes.leagueSeason }
							onChange={ ( leagueShortcut, leagueSeason ) => {
								setAttributes( {
									leagueShortcut,
									leagueSeason: parseInt( leagueSeason ),
								} );
							} }
						/>

						<CheckboxControl
							key={
								'openligadb-attribute-league-defaultcurrentgroup'
							}
							label={ __(
								'Aktuellen Spieltag anzeigen',
								'soccr'
							) }
							checked={ attributes.defaultCurrentGroup }
							onChange={ ( defaultCurrentGroup ) => {
								setAttributes( { defaultCurrentGroup } );
							} }
						/>

						{ attributes.defaultCurrentGroup === false && (
							<TextControl
								key={
									'openligadb-attribute-league-grouporderid'
								}
								type="number"
								min={ 1 }
								label={ __(
									'Spieltag (GroupOrderId)',
									'soccr'
								) }
								value={ attributes.groupOrderId ?? 1 }
								onChange={ ( groupOrderId ) => {
									setAttributes( {
										groupOrderId: parseInt( groupOrderId ),
									} );
								} }
							/>
						) }
						<CheckboxControl
							key={ 'openligadb-attribute-league-pagination' }
							label={ __( 'Blättern anzeigen', 'soccr' ) }
							checked={ attributes.pagination }
							onChange={ ( pagination ) => {
								setAttributes( { pagination } );
							} }
						/>
					</PanelBody>
				</Panel>
			</InspectorControls>

			<ServerSideRender
				key={ 'openligadb-group-matches-ssr' }
				block={ props.name }
				attributes={ props.attributes }
			/>
		</div>
	);
};

export default Edit;
