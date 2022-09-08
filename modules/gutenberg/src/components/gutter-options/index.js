const { __ } = wp.i18n

const gutterOptions = [
	{
		value: '0',
		label: __( 'None', 'cartflows-pro' ),
		shortName: __( 'None', 'cartflows-pro' ),
	},
	{
		value: '5',
		/* translators: abbreviation for small size */
		label: __( 'S', 'cartflows-pro' ),
		tooltip: __( 'Small', 'cartflows-pro' ),
	},
	{
		value: '10',
		/* translators: abbreviation for medium size */
		label: __( 'M', 'cartflows-pro' ),
		tooltip: __( 'Medium', 'cartflows-pro' ),
	},
	{
		value: '15',
		/* translators: abbreviation for large size */
		label: __( 'L', 'cartflows-pro' ),
		tooltip: __( 'Large', 'cartflows-pro' ),
	},
	{
		value: '20',
		/* translators: abbreviation for largest size */
		label: __( 'XL', 'cartflows-pro' ),
		tooltip: __( 'Huge', 'cartflows-pro' ),
	},
];

export default gutterOptions;