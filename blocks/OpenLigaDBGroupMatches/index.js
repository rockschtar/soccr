import { registerBlockType } from '@wordpress/blocks';
import metadata from './block.json';
import { default as Edit } from './Components/Edit'
import { groupMatchesIcon } from '../Components/icons';
import "./style.scss"

registerBlockType(metadata, {
    icon: groupMatchesIcon,
    edit : Edit,
    save(props) {
        return null;
    },
});
