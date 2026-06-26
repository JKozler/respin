const {registerBlockType} = wp.blocks; //Blocks API
const {createElement} = wp.element; //React.createElement
const {__} = wp.i18n; //translation functions
const {InspectorControls} = wp.editor; //Block inspector wrapper
const {TextControl, SelectControl, ServerSideRender} = wp.components; //Block inspector wrapper

registerBlockType('mw-custom-blocks/custom-content', {
	title: __('Předdefinovaný obsah'), // Block title.
	category: __('common'), //category
	attributes: {
		id: {
			default: 1,
		},
	},
	//display the edit interface + preview
	edit(props) {
		const attributes = props.attributes;
		const setAttributes = props.setAttributes;

		//Function to update id attribute
		function changeId(id) {
			setAttributes({id});
		}

		//Function to update heading level
		function changeHeading(heading) {
			setAttributes({heading});
		}

		//Display block preview and UI
		return createElement('div', {}, [
			//preview will go here
			createElement(ServerSideRender, {
				block: 'mw-custom-blocks/custom-content',
				attributes: attributes
			}),
			//Block inspector
			createElement(InspectorControls, {},
				[
					//A simple text control for post id
					createElement(TextControl, {
						value: attributes.id,
						label: __('Post Title'),
						onChange: changeId,
						type: 'number',
						min: 1,
						step: 1
					}),
				]
			)
		])
	},
	save() {
		return null;//save has to exist. This all we need
	}
});
