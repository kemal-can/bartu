<?php

return [
    'encoding'      => 'UTF-8',
    'finalize'      => true,
    'cachePath'     => storage_path('html-purifier-cache'),
    'cacheFileMode' => 0755,

    'settings' => [
        'default' => [
            // 'HTML.Doctype'             => 'HTML 4.01 Transitional',
            'HTML.Allowed'             => 'div,b,strong,i,em,u,a[href|title],ul,ol,li,p,br,span[data-mention-id|data-notified|data-mention-char|data-mention-value],img[width|height|alt|src],h1,h2,h3,h4,h5,h6,*[style|class]',
            'CSS.AllowedProperties'    => 'font,font-size,font-weight,font-style,font-family,text-decoration,padding-left,padding-right,padding,margin, margin-left,margin-right,color,background-color,text-align',
            'AutoFormat.AutoParagraph' => false,
            'AutoFormat.RemoveEmpty'   => false,
            'Attr.EnableID'            => true,
            'CSS.Trusted'              => true,
            'HTML.SafeIframe'          => true,
            'URI.SafeIframeRegexp'     => '%^(http://|https://|//)(www.youtube.com/embed/|player.vimeo.com/video/)%',
            'CSS.AllowTricky'          => true,
            'Attr.AllowedFrameTargets' => ['_blank'],

            // Images
            'URI.AllowedSchemes' => [
                  'http'   => true,
                  'https'  => true,
                  'mailto' => true,
                  'ftp'    => true,
                  'nntp'   => true,
                  'news'   => true,
                  'tel'    => true,
                   // Base64 Images
                  'data' => true,
            ],

            // These config option disables the pixel checks and allows
            // specifiy e.q. width="auto" or height="auto" for example on images
            'HTML.MaxImgLength' => null,
            'CSS.MaxImgLength'  => null,
        ],

        'custom_definition' => [
            'id'       => 'CustomHTML5',
            'rev'      => (int) str_replace('.', '', \App\Innoclapps\Application::VERSION),
            'debug'    => env('APP_DEBUG', false),
            'elements' => [
                // http://developers.whatwg.org/sections.html
                ['section', 'Block', 'Flow', 'Common'],
                ['nav',     'Block', 'Flow', 'Common'],
                ['article', 'Block', 'Flow', 'Common'],
                ['aside',   'Block', 'Flow', 'Common'],
                ['header',  'Block', 'Flow', 'Common'],
                ['footer',  'Block', 'Flow', 'Common'],

                // Content model actually excludes several tags, not modelled here
                ['address', 'Block', 'Flow', 'Common'],
                ['hgroup', 'Block', 'Required: h1 | h2 | h3 | h4 | h5 | h6', 'Common'],

                // http://developers.whatwg.org/grouping-content.html
                ['figure', 'Block', 'Optional: (figcaption, Flow) | (Flow, figcaption) | Flow', 'Common'],
                ['figcaption', 'Inline', 'Flow', 'Common'],

                // http://developers.whatwg.org/the-video-element.html#the-video-element
                ['video', 'Block', 'Optional: (source, Flow) | (Flow, source) | Flow', 'Common', [
                    'src'      => 'URI',
                    'type'     => 'Text',
                    'width'    => 'Length',
                    'height'   => 'Length',
                    'poster'   => 'URI',
                    'preload'  => 'Enum#auto,metadata,none',
                    'controls' => 'Bool',
                ]],

                ['source', 'Block', 'Flow', 'Common', [
                    'src'  => 'URI',
                    'type' => 'Text',
                ]],

                // http://developers.whatwg.org/text-level-semantics.html
                ['s',    'Inline', 'Inline', 'Common'],
                ['var',  'Inline', 'Inline', 'Common'],
                ['sub',  'Inline', 'Inline', 'Common'],
                ['sup',  'Inline', 'Inline', 'Common'],
                ['mark', 'Inline', 'Inline', 'Common'],
                ['wbr',  'Inline', 'Empty', 'Core'],

                // http://developers.whatwg.org/edits.html
                ['ins', 'Block', 'Flow', 'Common', ['cite' => 'URI', 'datetime' => 'CDATA']],
                ['del', 'Block', 'Flow', 'Common', ['cite' => 'URI', 'datetime' => 'CDATA']],
            ],

            'attributes' => [
                ['iframe', 'allowfullscreen', 'Bool'],
                ['table', 'height', 'Text'],
                ['td', 'border', 'Text'],
                ['th', 'border', 'Text'],
                ['tr', 'width', 'Text'],
                ['tr', 'height', 'Text'],
                ['tr', 'border', 'Text'],
            ],
        ],

        'custom_attributes' => [
            ['a', 'target', 'Enum#_blank,_self,_target,_top'],
            ['div', 'align', 'Enum#left,right,center'],

            // Mention
            ['span', 'data-mention-id', 'Number'],
            ['span', 'data-notified', 'Text'],
            ['span', 'data-mention-char', 'Text'],
            ['span', 'data-mention-value', 'Text'],
        ],

        'custom_elements' => [
            ['u', 'Inline', 'Inline', 'Common'],
            ['iframe', 'Inline', 'Flow', 'Common', [
                    'src'                   => 'URI#embedded',
                    'width'                 => 'Length',
                    'height'                => 'Length',
                    'name'                  => 'ID',
                    'scrolling'             => 'Enum#yes,no,auto',
                    'frameborder'           => 'Enum#0,1',
                    'allow'                 => 'Text',
                    'allowfullscreen'       => 'Bool',
                    'webkitallowfullscreen' => 'Bool',
                    'mozallowfullscreen'    => 'Bool',
                    'longdesc'              => 'URI',
                    'marginheight'          => 'Pixels',
                    'marginwidth'           => 'Pixels',
                ],
            ],
        ],
    ],
];
