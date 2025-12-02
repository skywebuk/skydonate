import { registerCheckoutBlockExtension } from '@woocommerce/checkout-blocks-registry';
import { TextControl } from '@wordpress/components';
import { useState } from '@wordpress/element';

// The rest of your logic follows...
registerCheckoutBlockExtension('my-plugin/checkout-field', () => {
    return {
        metadata: {
            name: 'custom-field',
        },
        component: () => {
            const [value, setValue] = useState('');

            const updateValue = (newValue) => {
                setValue(newValue);
                // Store in checkout context
                window.wc.checkout.blockCheckoutData.setExtensionData('my-plugin', {
                    custom_field: newValue,
                });
            };

            return (
                <div className="my-custom-field">
                    <TextControl
                        label="Custom Field"
                        value={value}
                        onChange={updateValue}
                        placeholder="Enter something..."
                    />
                </div>
            );
        },
    };
});
