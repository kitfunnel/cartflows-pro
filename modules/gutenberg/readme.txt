Components & Control Last Updated: 04-04-2022

=== How to update the spectra/ultimate-addon-for-gutenebrg components and controls in CartFlows Pro  ===

CartFlows has the Gutenberg blocks and it uses the Spectra's components to render the CartFlows blocks settings.
See the CartFlows-Pro > modules > gutenebrg > src > components
See the CartFlows-Pro > modules > gutenebrg > src > control
These are are the folders need to be update in the CartFlows-Pro.

Below are the steps to update the spectra/ultimate-addon-for-gutenebrg components and controls in CartFlows.

1. Get the latest version of the components and controls from https://github.com/brainstormforce/ultimate-addons-for-gutenberg

	Components => ultimate-addons-for-gutenberg/src/components
	Controls => ultimate-addons-for-gutenberg/blocks-config/uagb-controls

2. Copy the components folder from the Spectra to the CartFlows-Pro > modules > gutenebrg > src > components
3. Copy the uagb-controls folder from the Spectra to the CartFlows-Pro > modules > gutenebrg > src > control
4. Comment out the lazy loading CSS code in the each components and import respective CSS file directly.
5. In controls folder, go to renderIcons.js and on line no. 13 update the variable from const fontAwesome = uagb_blocks_info.uagb_svg_icons[ svg ]; to const fontAwesome = cf_blocks_info.wcf_svg_icons[ svg ];
6. Done

