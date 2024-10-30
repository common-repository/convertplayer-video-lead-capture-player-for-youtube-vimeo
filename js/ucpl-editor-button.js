(function() {
    tinymce.create('tinymce.plugins.ConvertPlayer', {
        init : function(ed, url) {
            var menuItems = [];
            if (typeof(ucpl_button_videos) != 'undefined') {
                for (var i = 0; i < ucpl_button_videos.length; i++) {
                    menuItems.push({
                        text: ucpl_button_videos[i].name,
                        onclick: (function(n) {
                            return function() {
                                var shortcode = '[convertplayer';
                                shortcode += ' id="' + ucpl_button_videos[n].id + '"';
                                shortcode += ' width="' + ucpl_button_videos[n].width + '"';
                                shortcode += ' height="' + ucpl_button_videos[n].height + '"';
                                shortcode += ']';
                                ed.insertContent(shortcode);
                            }
                        })(i)
                    });
                }
            }
            if (menuItems.length == 0) {
                menuItems.push({
                    text: 'No ConvertPlayer videos available.'
                });
            }
            ed.addButton('convert_player_button', {
                text: '',
                type: 'menubutton',
                title : 'Insert ConvertPlayer video shortcode',
                icon: 'convert_player_button_icon',
                menu: menuItems
            });
        },
        createControl : function(n, cm) {
            return null;
        },
        getInfo : function() {
            return {
                longname : 'Convert Player Button',
                author : 'Goat In The Boat Software',
                authorurl : 'http://goatintheboat.com/',
                infourl : 'https://convertplayer.com/',
                version : '1.0'
            };
        }
    });
    tinymce.PluginManager.add('ultimate_convert_player', tinymce.plugins.ConvertPlayer);
})();