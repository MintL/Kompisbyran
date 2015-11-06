<?php

namespace AppBundle\Enum;

class Languages
{
    /**
     * @var string[]
     */
    protected static $languages = [
        'aa' => 'afar',
        'ab' => 'abchaziska',
        'af' => 'afrikaans',
        'am' => 'amhariska',
        'ar' => 'arabiska',
        'as' => 'assamesiska',
        'ay' => 'aymara',
        'az' => 'azerbajdzjanska',
        'ba' => 'basjkiriska',
        'be' => 'vitryska',
        'bg' => 'bulgariska',
        'bh' => 'bihari',
        'bi' => 'bislama',
        'bn' => 'bengali',
        'bo' => 'tibetanska',
        'br' => 'bretonska',
        'ca' => 'katalanska',
        'co' => 'korsikanska',
        'cs' => 'tjeckiska',
        'cv' => 'tjuvasjiska',
        'cy' => 'kymriska',
        'da' => 'danska',
        'de' => 'tyska',
        'dz' => 'dzongkha',
        'el' => 'grekiska',
        'en' => 'engelska',
        'eo' => 'esperanto',
        'es' => 'spanska',
        'et' => 'estniska',
        'eu' => 'baskiska',
        'fa' => 'persiska',
        'fi' => 'finska',
        'fj' => 'fijianska',
        'fo' => 'färöiska',
        'fr' => 'franska',
        'fy' => 'frisiska',
        'ga' => 'iriska',
        'gd' => 'gaeliska',
        'gl' => 'galiciska',
        'gn' => 'guarani',
        'gu' => 'gujarati',
        'ha' => 'hausa',
        'he' => 'hebreiska',
        'hi' => 'hindi',
        'hr' => 'kroatiska',
        'hu' => 'ungerska',
        'hy' => 'armeniska',
        'ia' => 'interlingua',
        'id' => 'indonesiska',
        'ie' => 'interlingue',
        'ik' => 'iñupiaq',
        'is' => 'isländska',
        'it' => 'italienska',
        'iu' => 'inuktitut',
        'ja' => 'japanska',
        'jv' => 'javanesiska',
        'ka' => 'georgiska',
        'kk' => 'kazakiska',
        'kl' => 'grönländska',
        'km' => 'kambodjanska',
        'kn' => 'kannada',
        'ko' => 'koreanska',
        'ks' => 'kashmiri',
        'ku' => 'kurdiska',
        'ky' => 'kirgiziska',
        'la' => 'latin',
        'ln' => 'lingala',
        'lo' => 'laotiska',
        'lt' => 'litauiska',
        'lv' => 'lettiska',
        'mg' => 'madagaskiska',
        'mi' => 'maori',
        'mk' => 'makedonska',
        'ml' => 'malayalam',
        'mn' => 'mongoliska',
        'mo' => 'moldaviska',
        'mr' => 'marathi',
        'ms' => 'malajiska',
        'mt' => 'maltesiska',
        'my' => 'burmesiska',
        'na' => 'nauriska',
        'ne' => 'nepali',
        'nl' => 'nederländska',
        'no' => 'norska',
        'oc' => 'occitanska',
        'om' => 'afan oromo',
        'or' => 'oriya',
        'pa' => 'punjabi',
        'pl' => 'polska',
        'ps' => 'pashto',
        'pt' => 'portugisiska',
        'qu' => 'quechua',
        'rm' => 'rumantsch',
        'rn' => 'kirundi',
        'ro' => 'rumänska',
        'ru' => 'ryska',
        'rw' => 'kinyarwanda',
        'sa' => 'sanskrit',
        'sc' => 'sardiska',
        'sd' => 'sindhi',
        'se' => 'nordsamiska',
        'sg' => 'sangho',
        'sh' => 'serbokroatiska',
        'si' => 'singalesiska',
        'sk' => 'slovakiska',
        'sl' => 'slovenska',
        'sm' => 'samoanska',
        'sn' => 'shona',
        'so' => 'somaliska',
        'sq' => 'albanska',
        'sr' => 'serbiska',
        'ss' => 'siswati',
        'st' => 'sesotho',
        'su' => 'sundanesiska',
        'sv' => 'svenska',
        'sw' => 'swahili',
        'ta' => 'tamil',
        'te' => 'telugu',
        'tg' => 'tadzjikiska',
        'th' => 'thailändska',
        'ti' => 'tigrinja',
        'tk' => 'turkmenska',
        'tl' => 'tagalog',
        'tn' => 'setswana',
        'to' => 'tonganska',
        'tr' => 'turkiska',
        'ts' => 'tsonga',
        'tt' => 'tatariska',
        'tw' => 'twi',
        'ug' => 'uiguriska',
        'uk' => 'ukrainska',
        'ur' => 'urdu',
        'uz' => 'uzbekiska',
        'vi' => 'vietnamesiska',
        'vo' => 'volapük',
        'wo' => 'wolof',
        'xh' => 'xhosa',
        'yi' => 'jiddisch',
        'yo' => 'yoruba',
        'za' => 'zhuang',
        'zh' => 'kinesiska',
        'zu' => 'zulu',
    ];

    /**
     * @return string[]
     */
    public static function getList()
    {
        return static::$languages;
    }

    /**
     * @return string[]
     */
    public static function getActiveList()
    {
        $allowed  = ['sv', 'en', 'ar', 'de', 'es', 'fa', 'ku'];

        return array_filter(
            static::$languages,
            function ($key) use ($allowed) {
                return in_array($key, $allowed);
            },
            ARRAY_FILTER_USE_KEY
        );
    }

    /**
     * @param $code
     *
     * @return string
     */
    public static function getName($code)
    {
        if (!isset(static::$languages[$code])) {
            throw new \LogicException(sprintf('Language for code %s was not found.', $code));
        }

        return static::$languages[$code];
    }
}
