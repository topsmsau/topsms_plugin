<?php

/**
 * Provide an admin setup view for the plugin
 *
 * This file is used to markup the admin-facing aspects of the plugin.
 *
 * @link       https://eux.com.au
 * @since      1.0.0
 *
 * @package    Topsms
 * @subpackage Topsms/admin/partials/setup-steps
 */
?>

<!-- This file should primarily consist of HTML with a little bit of PHP. -->

<div class="topsms-registration-step">
    <div class="topsms-step-indicator">
        <div class="topsms-step active">
            <span class="topsms-step-number">1</span>
            <span class="topsms-step-label"><?php esc_html_e( 'Register', 'topsms' ); ?></span>
        </div>
        <div class="topsms-step">
            <span class="topsms-step-number">2</span>
            <span class="topsms-step-label"><?php esc_html_e( 'Confirm Phone Number', 'topsms' ); ?></span>
        </div>
        <div class="topsms-step">
            <span class="topsms-step-number">3</span>
            <span class="topsms-step-label"><?php esc_html_e( 'Welcome to TopSMS', 'topsms' ); ?></span>
        </div>
    </div>
    
    <div class="topsms-registration-container">
        <div class="topsms-registration-header">
            <div class="topsms-registration-icon">
                <svg width="48" height="48" viewBox="0 0 48 48" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <rect width="48" height="48" rx="24" fill="#F5F5F5"/>
                    <path d="M24 24C25.1046 24 26 23.1046 26 22C26 20.8954 25.1046 20 24 20C22.8954 20 22 20.8954 22 22C22 23.1046 22.8954 24 24 24Z" fill="#FF6B00"/>
                    <path d="M28 28C28 25.7909 26.2091 24 24 24C21.7909 24 20 25.7909 20 28" stroke="#FF6B00" stroke-width="1.5" stroke-linecap="round"/>
                </svg>
            </div>
            <h2 class="topsms-registration-title"><?php esc_html_e( 'Register', 'topsms' ); ?></h2>
            <p class="topsms-registration-description"><?php esc_html_e( 'Lorem ipsum dolor sit amet consectetur. Arcu sed aliquam blandit ut magna nullam magna sagittis.', 'topsms' ); ?></p>
        </div>
        
        <form id="topsms-registration-form" method="post" class="topsms-registration-form">
            <?php wp_nonce_field( 'topsms_registration', 'topsms_registration_nonce' ); ?>
            
            <div class="topsms-form-section">
                <h3 class="topsms-section-title"><?php esc_html_e( 'Profile details', 'topsms' ); ?></h3>
                
                <div class="topsms-form-row">
                    <div class="topsms-form-column">
                        <label for="topsms_first_name"><?php esc_html_e( 'First Name', 'topsms' ); ?></label>
                        <input type="text" id="topsms_first_name" name="topsms_first_name" placeholder="<?php esc_attr_e( 'Your first name', 'topsms' ); ?>" required />
                    </div>
                    <div class="topsms-form-column">
                        <label for="topsms_last_name"><?php esc_html_e( 'Last Name', 'topsms' ); ?></label>
                        <input type="text" id="topsms_last_name" name="topsms_last_name" placeholder="<?php esc_attr_e( 'Your last name', 'topsms' ); ?>" required />
                    </div>
                </div>
                
                <div class="topsms-form-row">
                    <div class="topsms-form-column">
                        <label for="topsms_company_name"><?php esc_html_e( 'Company Name', 'topsms' ); ?></label>
                        <input type="text" id="topsms_company_name" name="topsms_company_name" placeholder="<?php esc_attr_e( 'Your company name', 'topsms' ); ?>" required />
                    </div>
                    <div class="topsms-form-column">
                        <label for="topsms_phone"><?php esc_html_e( 'Phone Number', 'topsms' ); ?></label>
                        <div class="topsms-phone-input">
                            <div class="topsms-country-code" id="topsms-country-select">
                                <span class="topsms-flag">🇺🇸</span>
                                <span class="topsms-code">+1</span>
                                <span class="topsms-arrow">▼</span>
                            </div>
                            <input type="tel" id="topsms_phone" name="topsms_phone" placeholder="<?php esc_attr_e( '(333) 000-0000', 'topsms' ); ?>" required />
                            <input type="hidden" id="topsms_country_code" name="topsms_country_code" value="+1" />
                        </div>
                    </div>
                </div>
                
                <div class="topsms-form-row full-width">
                    <label for="topsms_email"><?php esc_html_e( 'Email', 'topsms' ); ?></label>
                    <input type="email" id="topsms_email" name="topsms_email" placeholder="<?php esc_attr_e( 'Your email address', 'topsms' ); ?>" required />
                </div>
                
                <div class="topsms-form-row">
                    <div class="topsms-form-column">
                        <label for="topsms_password"><?php esc_html_e( 'Password', 'topsms' ); ?></label>
                        <div class="topsms-password-field">
                            <input type="password" id="topsms_password" name="topsms_password" placeholder="<?php esc_attr_e( 'Create your password', 'topsms' ); ?>" required />
                            <span class="topsms-password-toggle" data-target="topsms_password">
                                <svg width="18" height="18" viewBox="0 0 18 18" fill="none" xmlns="http://www.w3.org/2000/svg">
                                    <path d="M7.41558 11.0407C6.94187 10.5669 6.66577 9.9346 6.66577 9.2407C6.66577 7.85301 7.79069 6.7281 9.17837 6.7281C9.86967 6.7281 10.5019 6.99683 10.9831 7.4781" stroke="#A0A0A0" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                    <path d="M11.4238 9.74072C11.2254 10.5294 10.6082 11.1617 9.81958 11.3751" stroke="#A0A0A0" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                    <path d="M4.87253 13.5844C3.64021 12.6281 2.60162 11.2331 1.91772 9.5C2.60902 7.75953 3.65561 6.35723 4.89532 5.39355C6.12022 4.42988 7.5299 3.91431 8.99997 3.91431C10.4851 3.91431 11.8948 4.43726 13.1271 5.4084" stroke="#A0A0A0" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                    <path d="M14.7524 7.125C15.2185 7.81629 15.61 8.61891 15.9149 9.5C14.5352 12.9805 11.9001 15.0856 9.00002 15.0856C8.30872 15.0856 7.63222 14.9613 6.99341 14.7258" stroke="#A0A0A0" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                    <path d="M15.0869 3.91284L2.91309 16.0867" stroke="#A0A0A0" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                </svg>
                            </span>
                        </div>
                    </div>
                    <div class="topsms-form-column">
                        <label for="topsms_password_confirm"><?php esc_html_e( 'Confirm Password', 'topsms' ); ?></label>
                        <div class="topsms-password-field">
                            <input type="password" id="topsms_password_confirm" name="topsms_password_confirm" placeholder="<?php esc_attr_e( 'Confirm your password', 'topsms' ); ?>" required />
                            <span class="topsms-password-toggle" data-target="topsms_password_confirm">
                                <svg width="18" height="18" viewBox="0 0 18 18" fill="none" xmlns="http://www.w3.org/2000/svg">
                                    <path d="M7.41558 11.0407C6.94187 10.5669 6.66577 9.9346 6.66577 9.2407C6.66577 7.85301 7.79069 6.7281 9.17837 6.7281C9.86967 6.7281 10.5019 6.99683 10.9831 7.4781" stroke="#A0A0A0" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                    <path d="M11.4238 9.74072C11.2254 10.5294 10.6082 11.1617 9.81958 11.3751" stroke="#A0A0A0" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                    <path d="M4.87253 13.5844C3.64021 12.6281 2.60162 11.2331 1.91772 9.5C2.60902 7.75953 3.65561 6.35723 4.89532 5.39355C6.12022 4.42988 7.5299 3.91431 8.99997 3.91431C10.4851 3.91431 11.8948 4.43726 13.1271 5.4084" stroke="#A0A0A0" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                    <path d="M14.7524 7.125C15.2185 7.81629 15.61 8.61891 15.9149 9.5C14.5352 12.9805 11.9001 15.0856 9.00002 15.0856C8.30872 15.0856 7.63222 14.9613 6.99341 14.7258" stroke="#A0A0A0" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                    <path d="M15.0869 3.91284L2.91309 16.0867" stroke="#A0A0A0" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                </svg>
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="topsms-form-section">
                <h3 class="topsms-section-title"><?php esc_html_e( 'Business Address', 'topsms' ); ?></h3>
                
                <div class="topsms-form-row full-width">
                    <label for="topsms_street_address"><?php esc_html_e( 'Street Address', 'topsms' ); ?></label>
                    <input type="text" id="topsms_street_address" name="topsms_street_address" placeholder="<?php esc_attr_e( 'Enter your address', 'topsms' ); ?>" required />
                </div>
                
                <div class="topsms-form-row full-width">
                    <label for="topsms_abn"><?php esc_html_e( 'ABN / ACN', 'topsms' ); ?></label>
                    <input type="text" id="topsms_abn" name="topsms_abn" placeholder="<?php esc_attr_e( 'Enter your ABN/ACN', 'topsms' ); ?>" required />
                </div>
                
                <div class="topsms-form-row">
                    <div class="topsms-form-column">
                        <label for="topsms_city"><?php esc_html_e( 'City', 'topsms' ); ?></label>
                        <input type="text" id="topsms_city" name="topsms_city" placeholder="<?php esc_attr_e( 'Enter your City', 'topsms' ); ?>" required />
                    </div>
                    <div class="topsms-form-column">
                        <label for="topsms_state"><?php esc_html_e( 'State / Province', 'topsms' ); ?></label>
                        <input type="text" id="topsms_state" name="topsms_state" placeholder="<?php esc_attr_e( 'Enter your Province', 'topsms' ); ?>" required />
                    </div>
                </div>
                
                <div class="topsms-form-row full-width">
                    <label for="topsms_postcode"><?php esc_html_e( 'Postcode', 'topsms' ); ?></label>
                    <input type="text" id="topsms_postcode" name="topsms_postcode" placeholder="<?php esc_attr_e( 'Postcode', 'topsms' ); ?>" required />
                </div>
            </div>
            
            <div class="topsms-form-actions">
                <button type="submit" class="button button-primary topsms-register-button">
                    <?php esc_html_e( 'Register', 'topsms' ); ?>
                </button>
                <div class="topsms-spinner spinner"></div>
            </div>
        </form>
    </div>
</div>
