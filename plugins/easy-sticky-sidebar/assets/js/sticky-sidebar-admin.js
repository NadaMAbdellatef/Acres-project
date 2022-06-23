/*
 *
 * neon maker backend end Javascript
 *
 * @since 1.0.0
 *
 */

const has_easy_sticky_sidebar_pro = function () {
    return wp.hooks.applyFilters('easy_sticky_sidebar_pro', false);
}

jQuery(document).ready(function ($) {
    height = $(".sticky-sidebar-button").height();
    $(".sticky-sidebar-button div").css("width", height);

    $(".easy-sidebar-wrap .sticky-sidebar-name-input + i").on("click", function () {
        $(this).prev().focus();
    });
});

var SSuprydp_Admin;
(function ($) {
    var $this, $total_results, $offset, $limit, $processed;
    SSuprydp_Admin = {
        settings: {},
        initilaize: function () {
            $this = SSuprydp_Admin;
            $(document).ready(function () {
                $offset = $processed = 0;
                $limit = 10;
                $this.onInitMethods();
            });
        },
        onInitMethods: function () { },
        ProcessPageData: function (event, elem) {
            event.preventDefault();
            jQuery(".ssuprydp_load").show();
            $(".upb_error").remove();

            $(elem).find('input[type="submit"]').attr("disabled", true);
            $this.postFormData(
                ajaxurl + "?action=process_pages",
                "#SSuprydp_form",
                function (response) {
                    if (response.status == "success") {
                        jQuery(".ssuprydp_load p").text(response.message);

                        redirect = "";
                        try {
                            redirect = new URL(response.redirect);
                        } catch (_) {
                            redirect = false;
                        }

                        if (redirect !== false) {
                            window.location.replace(response.redirect);
                        }

                        setTimeout(function () {
                            jQuery(".ssuprydp_load p").text("Loading.....");
                            jQuery(".ssuprydp_load").hide();
                        }, 1500);
                    } else {
                        jQuery(".ssuprydp_load p").text("Button text is required!");
                        setTimeout(function () {
                            jQuery(".ssuprydp_load p").text("Loading.....");
                            jQuery(".ssuprydp_load").hide();
                        }, 1500);
                    }
                }
            );
        },
        displayModal: function (response, sizeClass) {
            if (!sizeClass) {
                sizeClass = "modal-normal";
            }
            if (response.header) {
                $("#SSuprydp_modal .SSuprydp_modal_heading")
                    .html(response.header)
                    .show();
            } else {
                $("#SSuprydp_modal .SSuprydp_modal_heading").hide();
            }
            if (response.content) {
                $("#SSuprydp_modal_msg").show();
                $("#SSuprydp_modal_msg .SSuprydp_modal_content").html(response.content);
            } else {
                $("#SSuprydp_modal_msg").hide();
            }
            if (response.footer) {
                $("#SSuprydp_modal .SSuprydp_modal_footer")
                    .html(response.footer)
                    .show();
            } else {
                $("#SSuprydp_modal .SSuprydp_modal_footer").hide();
            }
            $("#SSuprydp_modal")
                .removeAttr("class")
                .addClass("upb_overlay " + sizeClass)
                .show();
        },
        postFormData: function (url, form, callback) {
            var formData = new FormData($(form)[0]);

            $.ajax({
                url: url, // server url
                type: "POST", //POST or GET
                data: formData, // data to send in ajax format or querystring format
                datatype: "json",
                beforeSend: function (xhr) { },
                success: function (data) {
                    callback(data); // return data in callback
                },

                complete: function () { },

                error: function (xhr, status, error) { },
                cache: false,
                contentType: false,
                processData: false,
            });
        },
    };
    SSuprydp_Admin.initilaize();
})(jQuery);

jQuery(function () {
    jQuery("#SSuprydp_button_option_font").fontselect();
    jQuery("#SSuprydp_content_option_font").fontselect();
    jQuery("#SSuprydp_action_option_font").fontselect();
});

jQuery(document).on("click", ".SSuprydp_dropdowm_list a", function () {
    var getfont = jQuery(this).find("i").attr("class");
    jQuery("#SSuprydp_awesome_font").val(getfont);
    jQuery("#SSuprydp_display_font").html('<i class="' + getfont + '"></i> ' + getfont);
});


jQuery(document).ready(function ($) {
    jQuery(".field-font-family").fontselect();

    $('.easy-sticky-sidebar-tab-panel > .tab-nav a').on('click', function (e) {
        e.preventDefault();
        href = $(this).attr('href');

        tab_panel = $(this).closest('.easy-sticky-sidebar-tab-panel');
        tab_navs = $(this).parent().children();

        tab_items = tab_panel.children('.easy-sticky-sidebar-tab-content').children();

        next_tab = tab_items.filter(href);

        if (!next_tab.length) {
            return;
        }

        $.cookie('easy-sticky-sidebar-import-export', href);

        tab_navs.removeClass('active');
        $(this).addClass('active');

        tab_items.not(next_tab).hide();
        next_tab.show();
    })

    export_tab_item = $.cookie('easy-sticky-sidebar-import-export');
    if (export_tab_item) {
        $(`.easy-sticky-sidebar-tab-panel > .tab-nav a[href="${export_tab_item}"]`).trigger('click')
    }

    $('ul.export-cta-list [data-select="all"]').on('change', function () {
        checked = $(this).is(':checked');
        $('ul.export-cta-list input[type="checkbox"]').not($(this)).prop('checked', checked)
    });


    $('[data-toggle="tooltip"]').tooltip({
        container: 'body'
    })

    $('ul#adminmenu .wp-submenu a[href="https://wordpressctapro.com/help"]').attr("target", "_blank");

    $(".nav-tab-wrapper.sticky-sidebar-nav-tab-wrapper .nav-tab").on("click", function (e) {
        e.preventDefault();
        target = $(this).attr("href");

        tab_contents = $(".sticky-sidebar-tab-content .tab-content");

        current = tab_contents.filter(target);
        if (!current.length) {
            return;
        }

        $('#SSuprydp_form [name="cta_editor_current_tab"]').val(target.replace('#', ''));

        //window.location.hash = target;

        $(".nav-tab-wrapper.sticky-sidebar-nav-tab-wrapper .nav-tab").not($(this)).removeClass("nav-tab-active");
        $(this).addClass("nav-tab-active");

        tab_contents.not(current).hide();
        current.show();
        return false;
    });

    window.EasyStickySidebar = {
        options: {
            template: 'sticky-cta'
        },

        set_state: function (key, value) {
            this[key] = value;

            wp.hooks.doAction('easy_sticky_sidebar_updated', this);
        },

        update_close_button_position: function () {
            current_template = $('[name="sidebar_template"]').val(), cta_position = $('[name="SSuprydp_cta_position"]').val();

            positions = { start: "Top", end: "Bottom" };
            if (cta_position == "top" || cta_position == "bottom") {
                positions = { start: 'Left', end: 'Right' }
            }

            if (current_template == 'gdpr') {
                positions = { 'top-left': 'Top Left', 'top-right': 'Top Right', 'bottom-left': 'Bottom Left', 'bottom-right': 'Bottom Right' }
            }

            positions = wp.hooks.applyFilters('easy_sticky_sidebar_close_button_positions', positions);
            const current_position = $('[name="close_button_position"]').attr('data-position');

            const options = Object.keys(positions).map((key) => {
                const selected_attr = key == current_position ? "selected" : "";
                return `<option value="${key}" ${selected_attr}>${positions[key]}</option>`;
            });

            $('[name="close_button_position"]').html(options.join(""));
        },

        init: function () {
            wp.hooks.doAction('easy_sticky_sidebar_init', this);
        }
    }

    $('.wp-list-table .column-action a.cta-delete').on('click', function (e) {
        response = confirm('Do you want to delete this item?');
        if (!response) {
            return e.preventDefault();
        }
    });

    let sidebar_name_timer = null;

    $(".wrap-easy-sticky-sidebar table.wp-list-table .sticky-sidebar-name-input").on("keydown", function (e) {
        if (sidebar_name_timer) {
            clearTimeout(sidebar_name_timer);
        }

        sidebar_name_timer = setTimeout(() => {
            jQuery.post(sticky_sidebar.ajax_url, {
                action: "change_sticky_sidebar_name",
                sticky: $(this).data("sticky"),
                name: $(this).val(),
            });
        }, 300)

        if (e.keyCode == 13) {
            $(this).blur();
            return false;
        }
    });

    $(".sticky-sidebar-colorpicker").wpColorPicker({
        change: function (event) {
            setTimeout(() => {
                $(event.target).trigger('change')
            }, 300)
        }
    });

    $(".sticky-cta-status-menu ul.statuses li").on("click", function () {
        status_item = $(this);
        menu_container = status_item.closest(".sticky-cta-status-menu");

        menu_container.find('[name="SSuprydp_development"]').val(status_item.data("status"));

        form = $('#SSuprydp_form');

        if (form.length) {
            form.attr('data-status', status_item.data("status"));
        }

        label = menu_container.children("label");
        const sticky_id = menu_container.data("id");

        if (sticky_id == 0) {
            label.html(status_item.html());
            label.attr("class", "status-" + status_item.data("status"));
            return;
        }

        const data = {
            action: "update_cta_status",
            sticky_id,
            status: status_item.data("status"),
        };

        $.post("/wp-admin/admin-ajax.php", data, function (response) {
            if (response.success == false) {
                return alert(response.error);
            }

            label.html(status_item.html());
            label.attr("class", "status-" + status_item.data("status"));
        });
    });

    var current_tab = "#sticky-sidebar-settings";
    if (window.location.hash === '#cta-location-options') {
        current_tab = '#sticky-sidebar-layout'
    }

    $('.nav-tab-wrapper.sticky-sidebar-nav-tab-wrapper .nav-tab[href="' + current_tab + '"]').trigger("click");

    var CTA_Button_Options_HTML = $('#sticky-sidebar-button-options > summary h2').html();


    const positon_options = $('[name="SSuprydp_cta_position"]').html();
    $('[name="sidebar_template"]').on("update", function (event, form_data) {
        if (form_data.template == 'banner') {
            $('#cta-content-options').show();
            $('#cta-link-text-options').show();

            current_position = $('[name="SSuprydp_cta_position"]').data('position');
            options = ['Top', 'Bottom'].map((pos) => {
                selected = current_position == pos.toLowerCase() ? 'selected' : '';
                return `<option value="${pos.toLowerCase()}" ${selected}>${pos}</option>`;
            })

            $('[name="SSuprydp_cta_position"]').html(options.join(''))
        } else {
            $('[name="SSuprydp_cta_position"]').html(positon_options)
        }
    })


    $('[name="sidebar_template"]').on("change", function () {
        const form_data = Object.assign({}, {
            template: $(this).val()
        });

        EasyStickySidebar.set_state('template', $(this).val())

        $("#SSuprydp_form").attr("data-template", form_data.template);

        if (form_data.template == "tab-cta") {
            $('#sticky-sidebar-button-options > summary h2').html('Tab Button Options')
        } else {
            $('#sticky-sidebar-button-options > summary h2').html(CTA_Button_Options_HTML)
        }

        $('[name="sidebar_template"]').trigger("update", form_data);

        $('#SSuprydp_form').trigger('update', form_data);

        $('.wordpress-cta-content-container > *').each(function (index) {
            order = (index + 1) * 2;
            $(this).css({ order })
        })
    }).trigger("change");

    // const Easy_Sticky_Sidebar_Positions = {
    //     position1: 'right',
    //     position2: 'center',

    //     get_position2_positions: function () {

    //     },

    //     set_position: function (value, target_position = 'position2') {
    //         this[target_position] = value;

    //         this.update_template();
    //     },


    //     update_template: function () {
    //         const self = this;

    //         let positions = { left: 'Left', center: 'Center', right: 'Right' }
    //         if (this.position1 == "left" || this.position1 == "right") {
    //             let positions = { top: "Top", center: "Center", bottom: "Bottom" };
    //         }

    //         const options = Object.keys(positions).map((key) => {
    //             const selected_attr = key == self.position2 ? "selected" : "";
    //             return `<option value="${key}" ${selected_attr}>${positions[key]}</option>`;
    //         });

    //         $('[name="horizontal_vertical_position"]').html(options.join(""));
    //     },

    //     init: function () {

    //     }
    // }


    $('[name="SSuprydp_cta_position"]').on("change", function () {
        cta_position = $(this).val();

        $(this).attr('data-position', cta_position)

        positions = { left: 'Left', center: 'Center', right: 'Right' }
        if (cta_position == "left" || cta_position == "right") {
            positions = { top: "Top", center: "Center", bottom: "Bottom" };
        }

        const current_position = $('[name="horizontal_vertical_position"]').attr("data-position");

        const options = Object.keys(positions).map((key) => {
            const selected_attr = key == current_position ? "selected" : "";
            return `<option value="${key}" ${selected_attr}>${positions[key]}</option>`;
        });

        $('[name="horizontal_vertical_position"]').html(options.join(""));

        //text alignment option fields
        button_text_alignment = $('[name="SSuprydp_button_option_align"]');


        text_alignments = { left: 'Top', center: 'Center', right: 'Bottom' };
        if (cta_position == "top" || cta_position == "bottom") {
            text_alignments = Object.assign(text_alignments, { left: 'Left', right: 'Right' });
        }

        const button_text_alignment_options = Object.keys(text_alignments).map((key) => {
            const selected_attr = key == button_text_alignment.attr('data-align') ? "selected" : "";
            return `<option value="${key}" ${selected_attr}>${text_alignments[key]}</option>`;
        });

        button_text_alignment.html(button_text_alignment_options.join(""));

        $('[name="horizontal_vertical_position"]').trigger('change')

    }).trigger("change");

    $('[name="horizontal_vertical_position"]').on('change', function () {
        const current_position = $(this).val();
        $(this).attr('data-position', current_position)
        $('#cta-horizontal-vertical-position').attr('data-position', current_position);
        $('#cta-horizontal-vertical-position .position2_distance-wrapper > label').attr('data-position', current_position);
    })

    $('[name="SSuprydp_button_option_align"]').on('change', function () {
        $(this).attr('data-align', $(this).val())
    })

    $('#SSuprydp_form [data-relative-fields]').on('change', function () {
        const value = $(this).data('relative-fields');
        if (!value) {
            return;
        }

        const relative_fields = $(value.replace(/,\s*$/, ""));
        if (!relative_fields.length) {
            return;
        }

        if ($(this).is(':checked')) {
            return relative_fields.show();
        }

        relative_fields.hide();
    }).trigger('change');


    const DesignTemplate = {
        template: null,

        get_values: function (style_only = false) {
            let values = {}

            try {
                values = JSON.parse(this.template)
                if (typeof values !== 'object') {
                    values = {};
                }

            } catch (error) {
                return {}
            }

            delete values['default_image'];

            if (style_only === false) {
                return values;
            }

            const remove_keys = [
                'SSuprydp_action_option_text', 'SSuprydp_button_option_text', 'SSuprydp_content_option_text', 'default_image', 'location_type', 'sticky_s_media',
                'button2_text', 'button2_url', 'image_attachment_id', 'tab_cta_url'
            ];
            remove_keys.forEach(key => delete values[key])
            return values;
        },

        init: function () {
            const self = this;

            $('#cta-premade-style').on('change', function (e) {
                $('#wordpress-cta-popup-load-design').trigger('open');
                self.template = $(this).val();
            });

            $('#wordpress-cta-popup-load-design .btn-cancel').on('click', function (e) {
                e.preventDefault();
                $('#wordpress-cta-popup-load-design').trigger('close');
                self.template = {};
            })

            $('#wordpress-cta-popup-load-design .btn-wordpress-cta-primary').on('click', function (e) {
                e.preventDefault();

                const style_only = $(this).attr('href') === '#load-style';
                const values = self.get_values(style_only);


                Object.keys(values).forEach((key) => {
                    input = $(`[name="${key}"]`);
                    if ($(`[name="${key}"][type="checkbox"]`).length) {
                        input = $(`[name="${key}"][type="checkbox"]`);
                    }

                    if (input.length) {
                        type = input.attr('type');

                        if (type == 'checkbox') {
                            const checked = ['Yes', 'yes', true].includes(values[key]) ? true : false;
                            input.prop('checked', checked).trigger('change');

                        } else {
                            input.prop('value', values[key]).trigger('change, input');
                            if (input.hasClass('wp-color-picker')) {
                                input.iris('color', values[key]);
                            }

                            const is_font = input.next('.font-select');
                            if (is_font.length) {
                                input.trigger('setFont', values[key]);
                            }
                        }
                    }
                })

                $('#image-preview').prop('src', $('#sticky_s_media').val());
                $('#wordpress-cta-popup-load-design').trigger('close');
            })
        }
    }
    DesignTemplate.init();


    const Wordpress_CTA_Popup = {
        container: $('.wordpress-cta-popup'),
        heading: 'This is a pro feature',
        description: null,

        init: function () {
            const self = this;

            $('.wordpress-cta-pro-element, .wordpress-cta-pro-feature-inline').on('click', function (e) {
                e.preventDefault();
                $('#wordpress-cta-pro-feature-popup').trigger('open');
            })

            self.container.on('open', function (event, data) {
                const popup_text = Object.assign({ heading: self.heading, description: self.description }, data)
                self.container.find('.pro-title').html(popup_text.heading)
                if (popup_text.description) {
                    self.container.find('.pro-description').html(popup_text.description)
                }

                $('body').addClass('has-wordpress-cta-popup');
                $(this).addClass('active');
            })

            self.container.on('close', function () {
                $('body').removeClass('has-wordpress-cta-popup')
                $(this).removeClass('active');
            })

            self.container.on('click', function (e) {
                if (self.container.is(e.target)) {
                    self.container.trigger('close')
                }
            })

            self.container.on('click', '.close', function () {
                $(this).closest('.wordpress-cta-popup').trigger('close')
            })

            $(document).on('keydown', function (e) {
                if (e.keyCode === 27) { // ESC
                    self.container.trigger('close')
                }
            });
        }
    }

    Wordpress_CTA_Popup.init();


    $('ul.wordpress-cta-dimension-field input[type="number"]').on('input', function () {
        const dimension_input = $(this).closest('ul.wordpress-cta-dimension-field');
        if (!dimension_input.hasClass('linked')) {
            return;
        }

        const value = $(this).val();
        dimension_input.find('input[type="number"]').val(value);
    })

    $('ul.wordpress-cta-dimension-field li.input-link').on('click', function () {
        const dimension_input = $(this).closest('ul.wordpress-cta-dimension-field');
        if (dimension_input.hasClass('linked')) {
            dimension_input.removeClass('linked');
            return;
        }

        dimension_input.addClass('linked')

        const first_value = dimension_input.children().eq(0).children('input').val();

        dimension_input.find('input[type="number"]').val(first_value)
    })

    $('ul.wordpress-cta-dimension-field').each(function () {
        const dimension_inputs = $(this).find('input[type="number"]');
        let first_value = dimension_inputs.eq(0).val();

        const values = [];
        dimension_inputs.each(function () {
            values.push($(this).val())
        })

        const get_values = values.filter(val => val === first_value)


        if (values.length === get_values.length) {
            $(this).addClass('linked');
        }
    })

    EasyStickySidebarIconLibrary = {
        popup: $('#easy-sticky-sidebar-icon-library-popup'),
        icon_items: $('#easy-sticky-sidebar-icon-library-popup .easy-sticky-sidebar-icon-grid .icon'),
        selected: '',
        onSelect: null,

        open: function (options) {
            this.onSelect = options?.onSelect;

            let selected_icon = options?.selected;
            if (selected_icon && selected_icon.length > 0) {
                selected_icon = '.' + selected_icon.replace(' ', '.');
            }

            const selected = this.icon_items.find(selected_icon);
            if (selected.length) {
                selected.closest('.icon').addClass('selected');
            }

            $('body').addClass('has-icon-library-popup')
            this.popup.addClass('opened');
        },

        close: function () {
            this.selected = '';
            this.icon_items.removeClass('selected')
            $('body').removeClass('has-icon-library-popup');
            this.popup.removeClass('opened');
        },

        init: function () {
            const self = this;

            self.popup.find('.dialog-header .close').on('click', function (e) {
                e.preventDefault();
                self.close();
            })

            self.popup.find('.easy-sticky-sidebar-icon-grid .icon').on('click', function (e) {
                e.preventDefault();

                self.icon_items.removeClass('selected');
                $(this).addClass('selected');
                self.selected = $(this).find('span').attr('class');
            })

            self.popup.find('.dialog-footer .btn-add-icon').on('click', function (e) {
                e.preventDefault();

                if (typeof self.onSelect === 'function') {
                    self.onSelect(self.selected)
                } else {
                    console.error('Callback function is not valid')
                }

                self.close();
            })

            $(document).on('keydown', function (e) {
                if (e.keyCode === 27) { // ESC
                    self.close();
                }
            });

            self.popup.on('click', function (e) {
                if (self.popup.is(e.target)) {
                    self.close()
                }
            })

            const search_form = self.popup.find('.form-search-icons');

            search_form.on('submit', function (e) {
                return false;
            })


            let typing = null;
            search_form.on('input', 'input', function (e) {
                if (typing) {
                    clearTimeout(typing)
                }

                typing = setTimeout(() => {
                    const search_text = $(this).val();
                    const keywords = search_text.split(' ').map((keyword) => keyword.trim().toLowerCase()).filter(t => t.length > 0);

                    if (keywords.length === 0) {
                        return self.icon_items.show();
                    }

                    jQuery.expr[':'].contains = function (a, i, m) {
                        return jQuery(a).text().toLowerCase().indexOf(m[3].toLowerCase()) >= 0;
                    };

                    let icons = self.icon_items.filter(':contains(' + keywords[0] + ')')
                    keywords.forEach((keyword, i) => {
                        if (i > 0) {
                            icons = icons.add(self.icon_items.filter(':contains(' + keyword + ')'))
                        }
                    })

                    self.icon_items.not(icons).hide();
                    icons.show();
                }, 300)
            })
        }
    }

    EasyStickySidebarIconLibrary.init();


    const FloatingButton = {
        buttons: {},
        button_default_args: {},
        container: $('#floating-buttons-options'),
        button_container: $('#floating-buttons-options .floating-buttons'),

        update_buttons: function (button_id = 0, event = 'update') {
            const tabs = $('#floating-single-button-styles').find('.easy-sticky-sidebar-fieldset-floating-button');
            const current_tab = tabs.filter(`[data-id="${button_id}"]`);

            if (event === 'remove') {
                current_tab.remove();
            }

            const button_args = { ...this.button_default_args, ...this.buttons[button_id] };


            const button_style_template = wp.template('easy-sticky-sidebar-floating-single-button-style');

            let tab_heading = '';
            if (button_args.icon.length) {
                tab_heading = `<i class="icon ${button_args.icon}"></i>`;
            }

            tab_heading += button_args.text;
            if (!tab_heading.length) {
                tab_heading = `Button ${button_id + 1}`;
            }

            button_args.button_no = button_id;
            button_args.heading = tab_heading;

            const html = button_style_template(button_args);

            if (current_tab.length) {
                current_tab.replaceWith(html)
            }

            if (event === 'add') {
                $('#floating-single-button-styles').append(html);
            }

            $('#floating-single-button-styles').find('.sticky-sidebar-colorpicker').wpColorPicker({
                change: function (event) {
                    setTimeout(() => {
                        $(event.target).trigger('change')
                    }, 300)
                }
            });
        },

        init: function () {
            const self = this;

            let button_default_args = self.container.data('button-default-args');
            if (typeof button_default_args === 'object') {
                self.button_default_args = button_default_args
            }

            const buttons = self.container.data('buttons');
            if (Array.isArray(buttons)) {
                buttons.forEach((button, key) => {
                    self.buttons[key] = button;
                })
            }

            self.container.on('change', '[type="checkbox"][name="hide_floating_button_text"]', function () {
                if ($(this).is(':checked')) {
                    $('#SSuprydp_form').addClass('hide-floating-button-text')
                } else {
                    $('#SSuprydp_form').removeClass('hide-floating-button-text')
                }
            }).trigger('change')

            self.container.on('click', '.btn-add-button', function (e) {
                e.preventDefault();

                let next_button_no = 0;

                const buttons = self.button_container.children();
                if (buttons.length) {
                    const last_button = buttons.last();
                    next_button_no = parseInt(last_button.attr('data-id')) + 1;
                }

                if (buttons.length >= 4 && !has_easy_sticky_sidebar_pro()) {
                    $('#wordpress-cta-pro-feature-popup').trigger('open', {
                        description: 'You need pro version for adding more buttons.'
                    });

                    return;
                }

                const button_template = wp.template("easy-sticky-sidebar-floating-button");
                const button_html = button_template({ button_no: next_button_no });
                self.button_container.append(button_html);
                self.buttons[next_button_no] = self.button_default_args;
                self.update_buttons(next_button_no, 'add');
            })

            self.container.on('click', '.btn-button-remove', function () {
                const button_container = $(this).closest('.floating-button-item')
                const button_id = button_container.attr('data-id');
                delete self.buttons[button_id];
                self.update_buttons(button_id, 'remove');
                button_container.remove();
            })

            self.button_container.on('input', '.button-text', function () {
                const button = $(this).closest('.floating-button-item');
                const button_id = button.attr('data-id');
                self.buttons[button_id]['text'] = $(this).val();
                self.update_buttons(button_id);
            })

            self.button_container.on('input', '.button-icon', function () {
                const button = $(this).closest('.floating-button-item');
                const button_id = button.attr('data-id');
                self.buttons[button_id]['icon'] = $(this).val();
                self.update_buttons(button_id);
            })

            self.button_container.on('click', '.sticky-sidebar-select-icon .btn-primary', function (e) {
                e.preventDefault();
                const container = $(this).closest('.sticky-sidebar-select-icon');

                EasyStickySidebarIconLibrary.open({
                    selected: container.find('input').val() || '',
                    onSelect: function (icon_class) {
                        container.find('input').val(icon_class).trigger('input');
                        container.find('.icon').attr('class', `icon ${icon_class}`);
                    }
                });
            })

            $('#floating-single-button-styles').on('input, change', '[data-name]', function () {
                const button_id = $(this).closest('.easy-sticky-sidebar-fieldset-floating-button').attr('data-id');
                const slug = $(this).data('name');
                if (!slug.length) {
                    return;
                }

                self.buttons[button_id][slug] = $(this).val();
            })

            wp.hooks.doAction('easy_sticky_sidebar_floating_button_init', self);
        }
    }

    FloatingButton.init();
});