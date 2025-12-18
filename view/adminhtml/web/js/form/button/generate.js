define([
    'Magento_Ui/js/form/components/button',
    'jquery',
    'Magento_Ui/js/modal/modal',
    'mage/translate'
], function (Button, $, modal, $t) {
    'use strict';

    return Button.extend({
        defaults: {
            elementTmpl: 'WeltPixel_ProductFeed/form/element/generate-button',
            visible: false,
            disabled: false,
            modalWindow: null
        },

        /**
         * Initialize component
         *
         * @returns {Object}
         */
        initialize: function () {
            this._super();

            // Check if feed exists
            var feedId = this.source.get('data.id');
            if (!feedId) {
                this.hide();
            }

            this.initModal();
            this.bindDefaultGenerateButton();

            return this;
        },

        /**
         * Bind events to default generate button
         */
        bindDefaultGenerateButton: function() {
            var self = this;

            // Find and bind the default generate button
            $(document).on('click', '#generate', function(e) {
                e.preventDefault();
                var feedId = self.source.get('data.id');

                if (feedId) {
                    var confirmationContent = $('<div></div>').html(
                        '<div class="message-confirmation">' +
                        $t('Are you sure you want to generate the feed? This process may take several minutes.') +
                        '</div>'
                    );

                    confirmationContent.modal({
                        title: $t('Generate Feed'),
                        modalClass: 'confirm weltpixel-feed-confirm',
                        content: confirmationContent,
                        buttons: [{
                            text: $t('Cancel'),
                            class: 'action-secondary action-dismiss',
                            click: function (event) {
                                this.closeModal(event);
                            }
                        }, {
                            text: $t('Generate'),
                            class: 'action-primary action-accept',
                            click: function (event) {
                                this.closeModal(event);
                                self.modalWindow.openModal();
                                self.generateFeed(feedId);
                            }
                        }]
                    }).trigger('openModal');
                }
            });
        },

        /**
         * Initialize modal window
         */
        initModal: function () {
            var modalHtml = $('<div></div>').append(
                $('<div></div>').load(require.toUrl('WeltPixel_ProductFeed/template/feed-generation-popup.html'))
            );

            var modalConfig = {
                type: 'popup',
                responsive: true,
                innerScroll: false,
                title: $t('Generating Feed'),
                modalClass: 'feed-generation-modal',
                buttons: [],
                closed: function() {
                }
            };

            this.modalWindow = modal(modalConfig, modalHtml);
        },

        /**
         * Generate feed via AJAX
         *
         * @param {Number} feedId
         */
        generateFeed: function(feedId) {
            var self = this;
            var $button = $('.generate.primary');

            $.ajax({
                url: this.generateUrl(feedId),
                type: 'GET',
                dataType: 'json',
                beforeSend: function() {
                    self.disabled(true);
                    $button.addClass('loading');
                }
            }).done(function(response) {
                if (response.error) {
                    self.modalWindow.closeModal();
                    $button.removeClass('loading').addClass('error');
                    self.showError(response.message);
                } else {
                    // Show success message
                    self.modalWindow.closeModal();
                    $button.removeClass('loading').addClass('success');
                    self.showSuccess(response.message);
                }
            }).fail(function(jqXHR) {
                self.modalWindow.closeModal();
                $button.removeClass('loading').addClass('error');
                self.showError($t('An error occurred while generating the feed.'));
            }).always(function() {
                self.disabled(false);
                // Reset button state after a delay
                setTimeout(function() {
                    $button.removeClass('loading success error');
                }, 3000);
            });
        },

        /**
         * Show success message
         *
         * @param {String} message
         */
        showSuccess: function(message) {
            $('<div></div>').html(message).modal({
                type: 'popup',
                modalClass: 'success',
                title: $t('Success'),
                buttons: [{
                    text: $t('OK'),
                    click: function () {
                        this.closeModal();
                    }
                }]
            }).trigger('openModal');
        },

        /**
         * Show error message
         *
         * @param {String} message
         */
        showError: function(message) {
            $('<div></div>').html(message).modal({
                type: 'popup',
                modalClass: 'error',
                title: $t('Error'),
                buttons: [{
                    text: $t('OK'),
                    click: function () {
                        this.closeModal();
                    }
                }]
            }).trigger('openModal');
        },

        /**
         * Generate URL for feed generation
         *
         * @param {Number} feedId
         * @returns {String}
         */
        generateUrl: function (feedId) {
            return window.weltpixelFeedGenerateUrl + 'id/' + feedId;
        },

        /**
         * Click handler
         */
        action: function () {
            var feedId = this.source.get('data.id');

            if (feedId) {
                var self = this;
                var confirmationContent = $('<div></div>').html(
                    '<div class="message-confirmation">' +
                    $t('Are you sure you want to generate the feed? This process may take several minutes.') +
                    '</div>'
                );

                confirmationContent.modal({
                    title: $t('Generate Feed'),
                    modalClass: 'confirm weltpixel-feed-confirm',
                    content: confirmationContent,
                    buttons: [{
                        text: $t('Cancel'),
                        class: 'action-secondary action-dismiss',
                        click: function (event) {
                            this.closeModal(event);
                        }
                    }, {
                        text: $t('Generate'),
                        class: 'action-primary action-accept',
                        click: function (event) {
                            this.closeModal(event);
                            self.modalWindow.openModal();
                            self.generateFeed(feedId);
                        }
                    }]
                }).trigger('openModal');
            }
        }
    });
});
