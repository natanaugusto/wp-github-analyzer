/* block.js */
const el = wp.element.createElement;

// This might be a React thing I forgot the name.
// I think it's something like component
const block = (props) => {
	return el(
		"div",
		{
			class: "github-analizer-block",
		},
		el("label", null, "GitHub Search:"),
		el("input", {
			id: "github-search",
			name: "github-search",
			type: "text",
			placeholder:
				props.attributes.hasOwnProperty("input_search_placeholder") &&
				props.attributes.input_search_placeholder !== null
					? props.attributes.input_search_placeholder
					: "GitHub Search...",
			onChange: props.hasOwnProperty("onChange") ? props.onChange : null,
		}),
		el(
			"div",
			{
				class: "results",
			},
			el(
				"ul",
				null,
				el(
					"div",
					{
						class: "header",
					},
					el(
						"span",
						{
							class: "title",
						},
						"Results"
					),
					el(
						"span",
						{
							class: "total",
						},
						"Total: ",
						el(
							"span",
							{
								class: "value",
							},
							"0"
						)
					)
				),
				el("li", null, "test")
			)
		)
	);
};

wp.blocks.registerBlockType("github-analyzer/github-analizer-block", {
	title: "GitHub",
	icon: "editor-code",
	category: "embed",

	attributes: {
		type: { type: "string", default: "default" },
		title: { type: "string" },
		content: { type: "array", source: "children", selector: "p" },
	},

	edit: (props) => {
		function updatePlaceholder(newdata) {
			props.setAttributes({ input_search_placeholder: newdata.target.value });
		}
		props.onChange = updatePlaceholder;
		return block(props);
	},
	save: (props) => {
		return block(props);
	},
});
