define([
    'Magento_Ui/js/grid/columns/column',
    'jquery',
    'mage/translate'
], function (Column, $, $t) {
    'use strict';

    return Column.extend({
        defaults: {
            bodyTmpl: 'WeltPixel_ProductFeed/grid/cells/feedfile'
        },

        getFieldHandler: function (record) {
            return false;
        },

        checkFileLink: function (row) {
            if (row['feed_file_url']) {
                return true;
            }
            return false;
        },

        checkFilePublicLink: function (row) {
            if (row['feed_file_link']) {
                return true;
            }
            return false;
        },

        /**
         * Copy feed URL to clipboard
         * @param {String} url
         */
        copyToClipboard: function(url) {
            var tempInput = document.createElement('input');
            tempInput.value = url;
            document.body.appendChild(tempInput);
            tempInput.select();
            document.execCommand('copy');
            document.body.removeChild(tempInput);

            // Visual feedback
            var $button = $('.copy-feed-url[data-url="' + url + '"]');
            $button.addClass('copied');


            // Reset button state after 2 seconds
            setTimeout(function() {
                $button.removeClass('copied');
            }, 2000);
        }
    });
});
