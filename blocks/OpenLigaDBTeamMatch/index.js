import { registerBlockType } from '@wordpress/blocks';
import metadata from './block.json';
import { default as Edit } from './Components/Edit';
import { teamMatchIcon } from '../Components/icons';
import './style.scss';

registerBlockType( metadata, {
	icon: teamMatchIcon,
	edit: Edit,
	save() {
		return null;
	},
} );
