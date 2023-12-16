import { InspectorControls } from '@wordpress/block-editor';
import { Panel, PanelBody, TextControl, CheckboxControl } from '@wordpress/components';
import { default as ServerSideRender } from '@wordpress/server-side-render';

const Edit = (props) => {
    const {
        setAttributes,
        attributes
    } = props;

    return [
        <InspectorControls key={'openligadb-standings-ic'}>
            <Panel key={'openligadb-standings-ic-panel'}>
                <PanelBody key={'openligadb-standings-ic-panel-body'}>
                    <TextControl key={'openligadb-standings-league-shortcut'}
                                 label="OpenLigaDB League Shortcut"
                                 value={attributes.leagueShortcut}
                                 onChange={(leagueShortcut) => { setAttributes({ leagueShortcut });}}
                    />
                    <TextControl key={'openligadb-standings-league-season'}
                                 type="number"
                                 label="OpenLigaDB League Season"
                                 value={attributes.leagueSeason}
                                 onChange={(leagueSeason) => {
                                     setAttributes({ leagueSeason: parseInt(leagueSeason) });
                                 }}
                    />
                    <CheckboxControl key={'openligadb-standings-hide-title'}
                                     label="Hide Title"
                                     onChange={(hideTitle) => {
                                         setAttributes({ hideTitle: hideTitle});
                                     }}
                    />
                </PanelBody>
            </Panel>
        </InspectorControls>,

        <ServerSideRender
          key={'openligadb-standings-ssr'}
          block={props.name}
          attributes={props.attributes}
        />,
    ];
}

export default Edit;
