import { InspectorControls, useBlockProps, RichText } from '@wordpress/block-editor';
import { Panel, PanelBody } from '@wordpress/components';
import { default as ServerSideRender } from '@wordpress/server-side-render';
import { LeagueSelectControl } from '../../Components/LeagueSelectControl';

const Edit = (props) => {
    const {
        setAttributes,
        attributes,
    } = props;

    const blockProps = useBlockProps();

    const placeholder = attributes.leagueShortcut
        ? `Tabelle | ${attributes.leagueShortcut.toUpperCase()} ${attributes.leagueSeason}`
        : 'Tabelle';

    return (
        <div {...blockProps}>
            <InspectorControls key={'openligadb-standings-ic'}>
                <Panel key={'openligadb-standings-ic-panel'}>
                    <PanelBody key={'openligadb-standings-ic-panel-body'}>
                        <LeagueSelectControl
                            leagueShortcut={attributes.leagueShortcut}
                            leagueSeason={attributes.leagueSeason}
                            autoSelect={true}
                            onChange={(leagueShortcut, leagueSeason) => {
                                setAttributes({ leagueShortcut, leagueSeason: parseInt(leagueSeason) });
                            }}
                        />
                    </PanelBody>
                </Panel>
            </InspectorControls>

            <RichText
                tagName="h2"
                value={attributes.title}
                onChange={(title) => setAttributes({ title })}
                placeholder={placeholder}
            />

            <ServerSideRender
                key={'openligadb-standings-ssr'}
                block={props.name}
                attributes={props.attributes}
            />
        </div>
    );
}

export default Edit;
