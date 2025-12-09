<?php

if (is_array($components) && !empty($components)) {

	foreach ($components as $component) {
		$name 			= array_key_exists('name', $component) ? $component['name'] : $component['id'];
		$button_id 		= array_key_exists('button_id', $component) ? $component['button_id'] : "";
		$button_text 	= array_key_exists('button_text', $component) ? $component['button_text'] : "";
		$description 	= isset($component['desc']) && !is_null($component['desc']) ? $component['desc'] : '';
		$class 			= array_key_exists('class', $component) ? esc_attr($component['class']) : '';
		$wrapper_class 	= array_key_exists('wrapper_class', $component) ? esc_attr($component['wrapper_class']) : '';
		$placeholder 	= array_key_exists('placeholder', $component) ? esc_attr($component['placeholder']) : '';
		$multiple 		= array_key_exists('multiple', $component) ? true : false;
		$multicheck 	= array_key_exists('multicheck', $component) ? true : false;
		$value 			= (isset($component['value']) && !empty($component['value'])) ? $component['value'] : (isset($component['default']) ? $component['default'] : "");
		$row 			= isset($component['row']) && !is_null($component['row']) ? $component['row'] : '5';
		$input_after	= isset($component['input_after']) ? $component['input_after'] : '';
		$input_before 	= isset($component['input_before']) ? $component['input_before'] : '';
		$checked 		= array_key_exists('checked', $component) ? $component['checked'] : '';
		$required 		= isset($component['required']) ? 'required' : '';
		$disabled 		= (isset($component['valid']) && $component['valid'] == 1) ? '' : 'disabled';

		switch ($component['type']) {
			case 'title':
				?>
				<h4 class="<?php echo $class; ?>" ><?php echo esc_html($component['label']); ?></h4>
				<?php
				break;
			case 'section_seperate':
				?>
				<p class="section-seperate" ></p>
				<?php
				break;
			case 'html':
				?>
				<h4 class="<?php echo $class; ?>" ><?php echo esc_html($component['label']); ?></h4>
				<?php
				echo $value;
				break;
				
			case 'hidden':
				?>
				<input type="hidden" id="<?php echo esc_attr($component['id']); ?>" name="<?php echo esc_attr($name); ?>" value="<?php echo $value; ?>"/>
				<?php
				break;
			case 'link':
				
				?>
				<div class="form-group <?php echo esc_attr($component['type']); ?>  <?php echo $wrapper_class; ?>">
					<div class="form-group-label">
						<label for="<?php echo esc_attr($component['id']); ?>" class="form-label"><?php echo esc_html($component['label']); // WPCS: XSS ok.       ?></label>
					</div>
					<div class="form-group-control">
						<div class="text-field text-field-outlined">
							<?php
							$url = sprintf('<a href="%s">%s</a>',$component['url'],$component['label']);
							echo $url;
							?>
						</div>
						<div class="text-field-helper-line">
							<div class="text-field-helper-text" id="" aria-hidden="true"><?php echo $description; ?></div>
						</div>
					</div>
				</div>
				<?php
				break;
				case 'price':
				$price_prefix = get_apca_currency_symbol();
				?>
				<div class="form-group <?php echo esc_attr($component['type']); ?>  <?php echo $wrapper_class; ?>">
					<div class="form-group-label">
						<label for="<?php echo esc_attr($component['id']); ?>" class="form-label"><?php echo esc_html($component['label']); // WPCS: XSS ok.       ?></label>
					</div>
					<div class="form-group-control">
						<div class="text-field text-field-outlined">
							<span><?php echo $price_prefix; ?></span>
							<input 
								class="text-field__input <?php echo $class; ?>" 
								name="<?php echo esc_attr($name); ?>"
								id="<?php echo esc_attr($component['id']); ?>"
								type="number"
								value="<?php echo $value; ?>"
								placeholder="<?php echo $placeholder; ?>"
								size="20"
								<?php echo $required;?>
								>
						</div>
						<div class="text-field-helper-line">
							<div class="text-field-helper-text" id="" aria-hidden="true"><?php echo $description; ?></div>
						</div>
					</div>
				</div>
				<?php
				break;
			case 'number':
				?>
				<div class="form-group <?php echo esc_attr($component['type']); ?>  <?php echo $wrapper_class; ?>">
					<div class="form-group-label">
						<label for="<?php echo esc_attr($component['id']); ?>" class="form-label"><?php echo esc_html($component['label']); // WPCS: XSS ok.       ?></label>
					</div>
					<div class="form-group-control">
						<div class="text-field text-field-outlined">
							<span class="input-before"><?php echo $input_before; ?></span>
							<input 
								class="text-field__input <?php echo $class; ?>" 
								name="<?php echo esc_attr($name); ?>"
								id="<?php echo esc_attr($component['id']); ?>"
								type="<?php echo esc_attr($component['type']); ?>"
								value="<?php echo $value; ?>"
								placeholder="<?php echo $placeholder; ?>"
								<?php echo $required;?>
								>
								<?php echo esc_attr($input_after); ?>
						</div>
						<div class="text-field-helper-line">
							<div class="text-field-helper-text" id="" aria-hidden="true"><?php echo $description; ?></div>
						</div>
					</div>
				</div>
				<?php
				break;
			case 'email':
				?>
				<div class="form-group <?php echo esc_attr($component['type']); ?>  <?php echo $wrapper_class; ?>">
					<div class="form-group-label">
						<label for="<?php echo esc_attr($component['id']); ?>" class="form-label"><?php echo esc_html($component['label']); // WPCS: XSS ok.       ?></label>
					</div>
					<div class="form-group-control">
						<div class="text-field text-field-outlined">
							<span class="input-before"><?php echo $input_before; ?></span>
							<input 
								class="text-field__input <?php echo $class; ?>" 
								name="<?php echo esc_attr($name); ?>"
								id="<?php echo esc_attr($component['id']); ?>"
								type="<?php echo esc_attr($component['type']); ?>"
								value="<?php echo $value; ?>"
								placeholder="<?php echo $placeholder; ?>"
								<?php echo $required;?>
								>
						</div>
						<div class="text-field-helper-line">
							<div class="text-field-helper-text" id="" aria-hidden="true"><?php echo $description; ?></div>
						</div>
					</div>
				</div>
				<?php
				break;
			case 'media':
				?>
				<div class="form-group <?php echo esc_attr($component['type']); ?> <?php echo $wrapper_class; ?>">
					<div class="form-group-label">
						<label for="<?php echo esc_attr($component['id']); ?>" class="form-label"><?php echo esc_html($component['label']); // WPCS: XSS ok.       ?></label>
					</div>
					<div class="form-group-control">
						<div class="text-field text-field-outlined">
							<input type="text" name="<?php echo esc_attr($name); ?>" value="<?php echo $value; ?>" class="regular-text"/>
							<input type="button" data-value="<?php echo esc_attr($name); ?>" class="button-secondary upload-media" value="Upload">
					   </div>
						<div class="text-field-helper-line">
							<div class="text-field-helper-text" id="" aria-hidden="true"><?php echo $description; ?></div>
						</div>
					</div>
				</div>
				<?php
				break;
			case 'text':
				?>
				<div class="form-group <?php echo esc_attr($component['type']); ?>  <?php echo $wrapper_class; ?>">
					<div class="form-group-label">
						<label for="<?php echo esc_attr($component['id']); ?>" class="form-label"><?php echo esc_html($component['label']); // WPCS: XSS ok.       ?></label>
					</div>
					<div class="form-group-control">
						<div class="text-field text-field-outlined">
							<span class="input-before"><?php echo $input_before; ?></span>
							<input 
								class="<?php echo $class; ?>" 
								name="<?php echo esc_attr($name); ?>"
								id="<?php echo esc_attr($component['id']); ?>"
								type="<?php echo esc_attr($component['type']); ?>"
								value="<?php echo $value; ?>"
								placeholder="<?php echo $placeholder; ?>"
								<?php echo $required;?>
								<?php echo esc_attr(LDIS); ?>>
						</div>
						<div class="text-field-helper-line">
							<div class="text-field-helper-text" id="" aria-hidden="true"><?php echo $description; ?></div>
						</div>
					</div>
				</div>
				<?php
				break;
				 case 'date':
				?>
				<div class="form-group <?php echo esc_attr($component['type']); ?>  <?php echo $wrapper_class; ?>">
					<div class="form-group-label">
						<label for="<?php echo esc_attr($component['id']); ?>" class="form-label"><?php echo esc_html($component['label']); // WPCS: XSS ok.       ?></label>
					</div>
					<div class="form-group-control">
						<div class="text-field text-field-outlined">
							<span class="input-before"><?php echo $input_before; ?></span>
							<input 
								class="<?php echo $class; ?>" 
								name="<?php echo esc_attr($name); ?>"
								id="<?php echo esc_attr($component['id']); ?>"
								type="<?php echo esc_attr($component['type']); ?>"
								value="<?php echo $value; ?>"
								placeholder="<?php echo $placeholder; ?>"
								<?php echo $required;?>
								>
						</div>
						<div class="text-field-helper-line">
							<div class="text-field-helper-text" id="" aria-hidden="true"><?php echo $description; ?></div>
						</div>
					</div>
				</div>
				<?php
				break;
			case 'textarea':
				?>
				<div class="form-group  <?php echo $wrapper_class; ?>">
					<div class="form-group-label">
						<label class="form-label" for="<?php echo esc_attr($component['id']); ?>"><?php echo esc_attr($component['label']); ?></label>
					</div>
					<div class="form-group-control">
						<div class="text-field text-field-outlined text-field-textarea">
							<span class="text-field-resizer">
							<span class="input-before"><?php echo $input_before; ?></span>
								<textarea class="<?php echo $class; ?>" rows="<?php echo $row; ?>" cols="60" aria-label="Label" name="<?php echo esc_attr($name); ?>" id="<?php echo esc_attr($component['id']); ?>" placeholder="<?php echo $placeholder; ?>" <?php echo $required;?>><?php echo esc_textarea($value); // WPCS: XSS ok.       ?></textarea>
							</span>
						</div>
						<div class="text-field-helper-line">
							<div class="text-field-helper-text" id="" aria-hidden="true"><?php echo $description; ?></div>
						</div>
					</div>
				</div>
				<?php
				break;
			case 'codeEditor':
				?>
				<div class="form-group  <?php echo $wrapper_class; ?>">
					<div class="form-group-label">
						<label class="form-label" for="<?php echo esc_attr($component['id']); ?>"><?php echo esc_attr($component['label']); ?></label>
					</div>
					<div class="form-group-control">
						<div class="text-field text-field-outlined text-field-textarea" for="text-field-hero-input">
							<textarea class="text-field-textarea <?php echo $class; ?>" rows="<?php echo $row; ?>" cols="25" aria-label="Label" name="<?php echo esc_attr($name); ?>" id="<?php echo esc_attr($component['id']); ?>" placeholder="<?php echo $placeholder; ?>" <?php echo $required;?>><?php echo $value; // WPCS: XSS ok.       ?></textarea>
						</div>
						<div class="text-field-helper-line">
							<div class="text-field-helper-text" id="" aria-hidden="true"><?php echo $description; ?></div>
						</div>
					</div>
				</div>
				<?php
				break;
			case 'wysiwyg':

				// default settings
				$content = $value;
				$editor_id = $component['id'];
				$editor_settings = array(
					'wpautop' => false, // use wpautop?
					'media_buttons' => false, // show insert/upload button(s)
					'textarea_name' => $editor_id, // set the textarea name to something different, square brackets [] can be used here
					'textarea_rows' => get_option('default_post_edit_rows', 10), // rows="..."
					'tabindex' => '',
					'editor_css' => '', // intended for extra styles for both visual and HTML editors buttons, needs to include the <style> tags, can use "scoped".
					'editor_class' => '', // add extra class(es) to the editor textarea
					'teeny' => false, // output the minimal editor config used in Press This
					'dfw' => false, // replace the default fullscreen with DFW (supported on the front-end in WordPress 3.4)
					'tinymce' => true, // load TinyMCE, can be used to pass settings directly to TinyMCE using an array()
					'quicktags' => true // load Quicktags, can be used to pass settings directly to Quicktags using an array()
				);
				?>
				<div class="form-group  <?php echo $wrapper_class; ?>">
					<div class="form-group-label">
						<label class="form-label" for="<?php echo esc_attr($component['id']); ?>"><?php echo esc_attr($component['label']); ?></label>
					</div>
					<div class="form-group-control">
						<div class="text-field text-field-outlined text-field-textarea"  for="text-field-hero-input">
							<?php
							$size = isset($component['size']) && !is_null($component['size']) ? $component['size'] : '500px';
							echo '<div style="max-width: ' . $size . ';">';
							wp_editor($value, esc_attr($component['id']), $editor_settings);
							echo '</div>';
							?>
						</div>
						<div class="text-field-helper-line">
							<div class="text-field-helper-text" id="" aria-hidden="true"><?php echo $description; ?></div>
						</div>
					</div>
				</div>
				<?php
				break;
			case 'select':
				?>
				<div class="form-group  <?php echo $wrapper_class; ?>">
					<div class="form-group-label">
						<label class="form-label" for="<?php echo esc_attr($component['id']); ?>"><?php echo esc_html($component['label']); ?></label>
					</div>
					<div class="form-group-control">
						<div class="form-select">
							<span class="input-before"><?php echo $input_before; ?></span>
							<select <?php echo esc_attr(LDIS); ?> name="<?php echo esc_attr($name); ?><?php echo ( true === $multiple ) ? '[]' : ''; ?>" id="<?php echo esc_attr($component['id']); ?>" class="mdl-textfield__input <?php echo $class; ?>" <?php echo ($multiple == true) ? 'multiple="multiple"' : ''; ?> <?php echo $required;?> <?php echo $disabled;?>>
								<?php
								$multiple_value = $value;
								foreach ($component['options'] as $field_key=>$field_value) {
									?>
									<option value="<?php echo strtolower($field_key); ?>"
									<?php
									if (is_array($component['value']) && $multiple == true) {
										selected(in_array(strtolower(strtolower($field_key)), $multiple_value), true);
									} else {
										selected(trim(strtolower($component['value'])),trim(strtolower($field_key)));
									}
									?>/>
									<?php echo esc_html($field_value); ?>
									</option>
									<?php
								}
								?>
							</select>
							<div class="text-field-helper-line">
								<div class="text-field-helper-text" id="" aria-hidden="true"><?php echo $description; ?></div>
							</div>
						</div>
					</div>
				</div>
				<?php
				break;
				case 'options':
				$option_values = $value;
				
				?>
				<div class="form-group  <?php echo $wrapper_class; ?>">
					<div class="form-group-label">
						<label class="form-label" for="<?php echo esc_attr($component['id']); ?>"><?php echo esc_html($component['label']); ?></label>
					</div>
					<div class="form-group-control">
						<div class="form-select">
							<input type="hidden" name="booking_quote_custom_field_options" value="1">
							<table class="table" width="100%" id="options-table">
								<thead>
									<tr>
										<td><?php _e('Value');?></td>
										<td><?php _e('Text');?></td>
									</tr>
								</thead>
								<tbody>
									<?php 
									$key = 0;
									if(!empty($option_values)):
									
									foreach($option_values as $option_value=>$option_text):
									$key++;
									?>
									<tr>
										<td><input type="text" name="option_value[]" value="<?php echo $option_value;?>" /></td>
										<td><input type="text" name="option_text[]" value="<?php echo $option_text;?>" /></td>
										<?php if($key >1):?>
											<td><button type="button" class="remove-options small-text"><?php _e('Remove','apca');?></button></td>
										<?php endif;?>
									</tr>
									<?php endforeach;?>
									<?php else:?>
									<tr>
										<td><input type="text" name="option_value[]" /></td>
										<td><input type="text" name="option_text[]" class="" /></td>
										<?php if($key >1):?>
											<td><button type="button" class="remove-options small-text"><?php _e('Remove','apca');?></button></td>
										<?php endif;?>
									</tr>
									<?php endif;?>
								</tbody>
								<tfoot>
									<tr>
										<td colspan="3"><button type="button" class="add-options"><?php _e('Add more','apca');?></button></td>
									</tr>
								</tfoot>
							</table>
							<div class="text-field-helper-line">
								<div class="text-field-helper-text" id="" aria-hidden="true"><?php echo $description; ?></div>
							</div>
						</div>
					</div>
				</div>
				<?php
				break;
				
			case 'checkbox':
			
			?>
				<div class="form-group  <?php echo $wrapper_class; ?>">
					<div class="form-group-label">
						<label for="<?php echo esc_attr($component['id']); ?>" class="form-label"><?php echo esc_html($component['label']); ?></label>
					</div>
					<div class="form-group-control">
						<div class="form-field">
							<?php if ($multicheck) { 
								$multicheck_value = get_post_meta($post_id, trim($component['id']), true);
								foreach ($component['options'] as $key => $checkbox_value) {
									$checked_value = isset($multicheck_value[$key]) ? $multicheck_value[$key] : "";
									?>
									<label class="checkbox-switch">
										<input 
											type="checkbox"
											name="<?php echo trim(esc_attr($name)); ?>[<?php echo $key; ?>]"
											id="<?php echo esc_attr($component['id'] . '_' . $key); ?>"
											value="<?php echo esc_attr($checkbox_value); ?>"
											<?php checked(trim($checked_value), trim($checkbox_value)); ?>
										>
										<span class="switch"></span>
										<small><?php echo esc_html($checkbox_value); ?></small>
									</label>
								<?php } 
							} else { ?>
								<label class="checkbox-switch">
									<input 
										type="checkbox"
										name="<?php echo esc_attr($name); ?>"
										id="<?php echo esc_attr($component['id']); ?>"
										value="<?php echo esc_attr($value); ?>"
										<?php if (!$disabled) { checked($checked, $value); } ?>
										<?php echo $required; ?>
										<?php echo $disabled; ?>
									>
									<span class="switch"></span>
									<small><?php echo esc_html($description); ?></small>
								</label>
							<?php } ?>
						</div>
					</div>
				</div>
				<?php
				
				break;
			case 'radio':
				?>
				<div class="form-group  <?php echo $wrapper_class; ?>">
					<div class="form-group-label">
						<label for="<?php echo esc_attr($component['id']); ?>" class="form-label"><?php echo esc_html($component['label']); ?></label>
					</div>
					<div class="form-group-control">
						<div class="form-field">
							<?php if ($multicheck) { ?>

								<?php

								
								foreach ($component['options'] as $checkbox_value) {
								   
									?>
									<div class="checkbox">
										<label>
										<input 
											name="<?php echo esc_attr($name); ?>"
											id="<?php echo esc_attr($component['id']); ?>"
											type="radio"
											class="checkbox-native-control <?php echo $class; ?>"
											value="<?php echo $checkbox_value; ?>"
											<?php checked(trim($value), trim($checkbox_value)); ?> 
											/>
											<?php
											echo $checkbox_value;
											?>
											</label>
										  </div>
										<?php }  ?>
							  
								<?php echo esc_html($description); // WPCS: XSS ok.  ?>

								<?php
							} else {
								$checked;
								//echo esc_attr(get_post_meta(743, '_quote_rides_shortcode_enforce_autocomplete_restriction', true));
								?>
								<div class="checkbox">
									<span class="input-before"><?php echo $input_before; ?></span>
									<input 
										name="<?php echo esc_attr($name); ?>"
										id="<?php echo esc_attr($component['id']); ?>"
										type="radio"
										class="checkbox-native-control <?php echo $class; ?>"
										value="<?php echo $value; ?>"
										<?php
										if ('1' === $checked) {
											checked($checked, $value);
										}
										?> <?php echo $required;?>/>

									<?php echo esc_html($description); // WPCS: XSS ok.   ?>
								</div>
							<?php } ?>
						</div>
					</div>
				</div>
				<?php
				break;
			case 'color':
				?>
				<div class="form-group <?php echo esc_attr($component['type']); ?>  <?php echo $wrapper_class; ?>">
					<div class="form-group-label">
						<label for="<?php echo esc_attr($component['id']); ?>" class="form-label"><?php echo esc_html($component['label']); // WPCS: XSS ok.      ?></label>
					</div>
					<div class="form-group-control color-fields">
						<div>
							<input 
								class="text-field-input colorpicker <?php echo $class; ?>" 
								name="<?php echo esc_attr($name); ?>"
								id="<?php echo esc_attr($component['id']); ?>"
								type="text"
								value="<?php echo esc_attr($value); ?>"
								placeholder="<?php echo $placeholder; ?>"
								<?php echo $required;?>
								>
						</div>
					</div>
				</div>

				<?php
				break;
			case 'password':
				?>
				<div class="form-group  <?php echo $wrapper_class; ?>">
					<div class="form-group-label">
						<label for="<?php echo esc_attr($component['id']); ?>" class="form-label"><?php echo esc_html($component['label']); // WPCS: XSS ok.      ?></label>
					</div>
					<div class="form-group-control">
							<span class="generate-password"><i class="material-icons" tabindex="0" role="button">Generate</i></span>
						<div class="text-field text-field-outlined text-field-with-trailing-icon">
							<input 
								class="text-field__input <?php echo $class; ?> form__password" 
								name="<?php echo esc_attr($name); ?>"
								id="<?php echo esc_attr($component['id']); ?>"
								type="<?php echo esc_attr($component['type']); ?>"
								value="<?php echo esc_attr($value); ?>"
								placeholder="<?php echo $placeholder; ?>"
								<?php echo $required;?>
								>
							<span class="password-visible"><i class="material-icons password-hidden" tabindex="0" role="button">visibility</i></span>
							
						</div>
						<div class="text-field-helper-line">
							<div class="text-field-helper-text" id="" aria-hidden="true"><?php echo $description; ?></div>
						</div>
					</div>
				</div>
				<?php
				break;
			case 'nonce':
				wp_nonce_field(esc_attr($component['name']), esc_attr($component['id']));
				break;
			case 'heading':
				?>
				<div class="form-group  <?php echo $wrapper_class; ?>">
					<?php
					echo '<' . esc_attr($component['tag']) . ' class="' . $class . '">' . esc_attr($component['label']) . '</' . esc_attr($component['tag']) . '>';
					?>
				</div>
				<?php
				break;
			case 'button':
				?>
				<div class="form-group  <?php echo $wrapper_class; ?>">
					<div class="form-group__label"></div>
					<div class="form-group__control">
						<button class="skydonation-button button button--raised <?php echo $class; ?>" name="<?php echo esc_attr($name); ?>"
								id="<?php echo esc_attr($component['id']); ?>"> <span class="button__ripple"></span>
							<span class="button__label"><?php echo esc_attr($component['button_text']); ?></span>
						</button>
					</div>
				</div>
				<?php
				break;

			case 'submit':
				?>
				<div class="form-group  <?php echo $wrapper_class; ?>">
					<div class="form-group__label"></div>
					<div class="form-group__control">
						<input type="submit" class="button button-primary" 
							   name="<?php echo esc_attr($name); ?>"
							   id="<?php echo esc_attr($component['id']); ?>"
							   value="<?php echo esc_attr($component['label']); ?>"
							   />
					</div>
				</div>
				<?php
				break;
			default:
				break;
		}
	}
}