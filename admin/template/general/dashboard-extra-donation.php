<?php

$products = Skyweb_Donation_Functions::Get_Title('product', 'ids');


$saved_donations = get_option('skydonation_extra_donation_items', []);

?>

<div class="skyweb-settings-panel">
    <form class="skydonation-extra-donation-form" method="post" action="">
        <input type="hidden" name="action" value="save_sky_donation_settings">
        <?php wp_nonce_field('save_sky_donation_settings', 'sky_donation_nonce'); ?>
        
        <table class="form-table">
            <tr>
                <th scope="row">
                    <label><?php _e('Donation Fields', 'skydonation'); ?></label>
                </th>
                <td>
                    <div id="extra-donation-options-container">
                        
                        <?php if (!empty($saved_donations)) : ?>
                            <?php foreach ($saved_donations as $donation) : ?>
                                <div class="donation-accordion">
                                    <div class="donation-header">
                                        <strong><?php _e('Donation Option', 'skydonation'); ?></strong>
                                        <button type="button" class="toggle-fields">▼</button>
                                    </div>
                                    <div class="donation-fields" style="display:none;">
                                        <button type="button" class="remove-option">×</button>
                                        <p>
                                            <label><?php _e('Select Donation', 'skydonation'); ?></label><br>
                                            <select name="donation_item[][id]">
                                                <?php foreach ($products as $id => $name) : ?>
                                                    <option value="<?php echo esc_attr($id); ?>" <?php selected($id, $donation['id']); ?>>
                                                        <?php echo esc_html($name); ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                        </p>

                                        <p>
                                            <label><?php _e('Donation Amount', 'skydonation'); ?></label><br>
                                            <input type="number" name="donation_item[][amount]" value="<?php echo esc_attr($donation['amount']); ?>" min="1" step="any" />
                                        </p>

                                        <p>
                                            <label><?php _e('Donation Title', 'skydonation'); ?></label><br>
                                            <input type="text" name="donation_item[][title]" value="<?php echo esc_attr($donation['title'] ?? ''); ?>" placeholder="Enter a custom donation title." />
                                        </p>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php else : ?>
                            <!-- Default empty donation option -->
                            <div class="donation-accordion">
                                <div class="donation-header">
                                    <strong><?php _e('Donation Option', 'skydonation'); ?></strong>
                                    <button type="button" class="toggle-fields">▼</button>
                                </div>
                                <div class="donation-fields" style="display:none;">
                                    <button type="button" class="remove-option">×</button>

                                    <p>
                                        <label><?php _e('Select Donation', 'skydonation'); ?></label><br>
                                        <select name="donation_item[][id]">
                                            <?php foreach ($products as $id => $name) : ?>
                                                <option value="<?php echo esc_attr($id); ?>"><?php echo esc_html($name); ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </p>

                                    <p>
                                        <label><?php _e('Donation Amount', 'skydonation'); ?></label><br>
                                        <input type="number" name="donation_item[][amount]" value="10" min="1" step="any" />
                                    </p>

                                    <p>
                                        <label><?php _e('Donation Title', 'skydonation'); ?></label><br>
                                        <input type="text" name="donation_item[][title]" value="" placeholder="Enter a custom donation title." />
                                    </p>
                                </div>
                            </div>
                        <?php endif; ?>
                        
                    </div>

                    <!-- Add Fields Button -->
                    <p>
                        <button type="button" class="button add-donation-option"><?php _e('Add Fields', 'skydonation'); ?></button>
                    </p>

                </td>
            </tr>
        </table>

        <br>
        <p>
            <button type="submit" class="skydonation-button">
                <?php _e('Save Settings', 'skydonation'); ?>
            </button>
        </p>
    </form>
</div>

<script>
jQuery(document).ready(function($){

    const container = $('#extra-donation-options-container');

    // Toggle accordion (only one open at a time)
    container.on('click', '.donation-header', function(){
        const currentAccordion = $(this).closest('.donation-accordion');

        // Close all other accordions
        container.find('.donation-accordion').not(currentAccordion).find('.donation-fields').slideUp();

        // Toggle the clicked one
        currentAccordion.find('.donation-fields').slideToggle();
    });

    // Add new donation option
    $('.add-donation-option').on('click', function(){
        let newOption = container.find('.donation-accordion:first').clone();

        // Clear input/select values
        newOption.find('input').val('');
        newOption.find('select').prop('selectedIndex', 0);

        // Close all other accordions
        container.find('.donation-accordion .donation-fields').slideUp();

        // Show fields of the new clone
        newOption.find('.donation-fields').slideDown();

        // Append to container
        container.append(newOption);
    });

    // Remove donation option
    container.on('click', '.remove-option', function(){
        if(container.find('.donation-accordion').length > 1){
            $(this).closest('.donation-accordion').remove();
        }
    });

});
</script>
