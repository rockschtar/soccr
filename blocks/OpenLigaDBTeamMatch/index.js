import { registerBlockType } from '@wordpress/blocks';
import metadata from './block.json';
import { default as Edit } from './Components/Edit'
import "./style.scss"

registerBlockType(metadata, {
    edit : Edit,
    save(props) {
        return null;
    },
});
