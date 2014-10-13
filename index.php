<?php

/**
 * moziloCMS Plugin: GradeGen
 *
 * A generator for various multiple exercises of different subjects.
 *
 * PHP version 5
 *
 * @category PHP
 * @package  PHP_MoziloPlugins
 * @author   DEVMOUNT <mail@devmount.de>
 * @license  GPL v3+
 * @version  GIT: v0.1.2014-08-25
 * @link     https://github.com/devmount/GradeGen
 * @link     http://devmount.de/Develop/moziloCMS/Plugins/GradeGen.html
 * @see      So do not fear, for I am with you; do not be dismayed, for I am your
 *           God. I will strengthen you and help you; I will uphold you with my
 *           righteous right hand.
 *           – The Bible
 *
 * Plugin created by DEVMOUNT
 * www.devmount.de
 *
 */

// only allow moziloCMS environment
if (!defined('IS_CMS')) {
    die();
}

/**
 * GradeGen Class
 *
 * @category PHP
 * @package  PHP_MoziloPlugins
 * @author   DEVMOUNT <mail@devmount.de>
 * @license  GPL v3+
 * @link     https://github.com/devmount/GradeGen
 */
class GradeGen extends Plugin
{
    // language
    private $_admin_lang;
    private $_cms_lang;

    // plugin information
    const PLUGIN_AUTHOR  = 'DEVMOUNT';
    const PLUGIN_TITLE   = 'GradeGen';
    const PLUGIN_VERSION = 'v0.1.2014-08-25';
    const MOZILO_VERSION = '2.0';
    const PLUGIN_DOCU
        = 'http://devmount.de/Develop/moziloCMS/Plugins/GradeGen.html';

    private $_plugin_tags = array(
        'tag1' => '{GradeGen}',
    );

    const LOGO_URL = 'http://media.devmount.de/logo_pluginconf.png';

    /**
     * set configuration elements, their default values and their configuration
     * parameters
     *
     * @var array $_confdefault
     *      text     => default, type, maxlength, size, regex
     *      textarea => default, type, cols, rows, regex
     *      password => default, type, maxlength, size, regex, saveasmd5
     *      check    => default, type
     *      radio    => default, type, descriptions
     *      select   => default, type, descriptions, multiselect
     */
    private $_confdefault = array(
        // 'text' => array(
        //     'string',
        //     'text',
        //     '100',
        //     '5',
        //     "/^[0-9]{1,3}$/",
        // ),
        // 'textarea' => array(
        //     'string',
        //     'textarea',
        //     '10',
        //     '10',
        //     "/^[a-zA-Z0-9]{1,10}$/",
        // ),
        // 'password' => array(
        //     'string',
        //     'password',
        //     '100',
        //     '5',
        //     "/^[a-zA-Z0-9]{8,20}$/",
        //     true,
        // ),
        // 'check' => array(
        //     true,
        //     'check',
        // ),
        // 'radio' => array(
        //     'red',
        //     'radio',
        //     array('red', 'green', 'blue'),
        // ),
        // 'select' => array(
        //     'bike',
        //     'select',
        //     array('car','bike','plane'),
        //     false,
        // ),
    );

    /**
     * creates plugin content
     *
     * @param string $value Parameter divided by '|'
     *
     * @return string HTML output
     */
    function getContent($value)
    {
        global $CMS_CONF;
        global $syntax;

        // initialize cms lang
        $this->_cms_lang = new Language(
            $this->PLUGIN_SELF_DIR
            . 'lang/cms_language_'
            . $CMS_CONF->get('cmslanguage')
            . '.txt'
        );

        // get params
        $mode = $value; // TODO: build modes

        // get conf and set default
        $conf = array();
        foreach ($this->_confdefault as $elem => $default) {
            $conf[$elem] = ($this->settings->get($elem) == '')
                ? $default[0]
                : $this->settings->get($elem);
        }

        // initialize return content, begin plugin content
        $content = '<!-- BEGIN ' . self::PLUGIN_TITLE . ' plugin content --> ';

        $content .= '<div class="gradegen">';

        // build form
        $reached_selected = (getRequestValue('mode') == 'reached') ? 'selected' : '';
        $error_selected = (getRequestValue('mode') == 'error') ? 'selected' : '';
        $content .= '
            <h2>Konfiguration</h2>
            <div class="section"><div>
                <form name="gradegen-form" action="" method="post">
                    <h3>Punkte</h3>
                    <input type="number" name="total" value="'
                        . getRequestValue('total')
                    . '" required /> Gesamtpunkte<br />
                    <input type="number" name="points" value="'
                        . getRequestValue('points')
                    . '" required />
                    <select name="mode">
                        <option value="reached" ' . $reached_selected . '>erreichte Punkte</option>
                        <option value="error" ' . $error_selected . '>Fehlerpunkte</option>
                    </select>
                    <h3>Maßstab</h3>
                    ab <input type="number" name="grade1" value="'
                        . getRequestValue('grade1')
                    . '" required /> % Note 1<br />
                    ab <input type="number" name="grade2" value="'
                        . getRequestValue('grade2')
                    . '" required /> % Note 2<br />
                    ab <input type="number" name="grade3" value="'
                        . getRequestValue('grade3')
                    . '" required /> % Note 3<br />
                    ab <input type="number" name="grade4" value="'
                        . getRequestValue('grade4')
                    . '" required /> % Note 4<br />
                    ab <input type="number" name="grade5" value="'
                        . getRequestValue('grade5')
                    . '" required /> % Note 5<br />
                    sonst Note 6<br />
                    <input type="submit" name="gradegen" value="Start" />
                </form>
            </div></div>
        ';

        // handle input and build exercises
        if (getRequestValue('gradegen') != '') {
            // get configuration data
            $points = array(
                'total' => getRequestValue('total'),
                'points' => getRequestValue('points'),
                'mode' => getRequestValue('mode'),
            );
            $grades = array(
                '1' => getRequestValue('grade1'),
                '2' => getRequestValue('grade2'),
                '3' => getRequestValue('grade3'),
                '4' => getRequestValue('grade4'),
                '5' => getRequestValue('grade5'),
            );
            // multiply
            $content .= '<br />';
            $content .= '<h2>Ausgabe</h2>';
            $content .= '<div class="section"><div>';

            switch ($points['mode']) {
                case 'reached':
                    $percentage = round(($points['points']/$points['total'])*100, 2);
                    break;
                 case 'error':
                    $percentage = round((1-($points['points']/$points['total']))*100, 2);
                    break;
                default:
                    $percentage = 0;
                    break;
            }
            $content .= $percentage . ' % erreicht<br />';

            if ($percentage < $grades['5']) {
                $grade = "6";
            }
            if ($percentage >= $grades['5'] and $percentage < $grades['4']) {
                $grade = "5";
            }
            if ($percentage >= $grades['4'] and $percentage < $grades['3']) {
                $grade = "4";
            }
            if ($percentage >= $grades['3'] and $percentage < $grades['2']) {
                $grade = "3";
            }
            if ($percentage >= $grades['2'] and $percentage < $grades['1']) {
                $grade = "2";
            }
            if ($percentage >= $grades['1']) {
                $grade = "1";
            }
            $content .= 'Note ' . $grade;

            $content .= '</div></div>';
            $content .= '<br />Alle Angaben und Ergebnisse ohne Gewähr.';
        }

        $content .= '</div>';

        // end plugin content
        $content .= '<!-- END ' . self::PLUGIN_TITLE . ' plugin content --> ';

        return $content;
    }

    /**
     * sets backend configuration elements and template
     *
     * @return Array configuration
     */
    function getConfig()
    {
        $config = array();

        // read configuration values
        foreach ($this->_confdefault as $key => $value) {
            // handle each form type
            switch ($value[1]) {
            case 'text':
                $config[$key] = $this->confText(
                    $this->_admin_lang->getLanguageValue('config_' . $key),
                    $value[2],
                    $value[3],
                    $value[4],
                    $this->_admin_lang->getLanguageValue(
                        'config_' . $key . '_error'
                    )
                );
                break;

            case 'textarea':
                $config[$key] = $this->confTextarea(
                    $this->_admin_lang->getLanguageValue('config_' . $key),
                    $value[2],
                    $value[3],
                    $value[4],
                    $this->_admin_lang->getLanguageValue(
                        'config_' . $key . '_error'
                    )
                );
                break;

            case 'password':
                $config[$key] = $this->confPassword(
                    $this->_admin_lang->getLanguageValue('config_' . $key),
                    $value[2],
                    $value[3],
                    $value[4],
                    $this->_admin_lang->getLanguageValue(
                        'config_' . $key . '_error'
                    ),
                    $value[5]
                );
                break;

            case 'check':
                $config[$key] = $this->confCheck(
                    $this->_admin_lang->getLanguageValue('config_' . $key)
                );
                break;

            case 'radio':
                $descriptions = array();
                foreach ($value[2] as $label) {
                    $descriptions[$label] = $this->_admin_lang->getLanguageValue(
                        'config_' . $key . '_' . $label
                    );
                }
                $config[$key] = $this->confRadio(
                    $this->_admin_lang->getLanguageValue('config_' . $key),
                    $descriptions
                );
                break;

            case 'select':
                $descriptions = array();
                foreach ($value[2] as $label) {
                    $descriptions[$label] = $this->_admin_lang->getLanguageValue(
                        'config_' . $key . '_' . $label
                    );
                }
                $config[$key] = $this->confSelect(
                    $this->_admin_lang->getLanguageValue('config_' . $key),
                    $descriptions,
                    $value[3]
                );
                break;

            default:
                break;
            }
        }

        // read admin.css
        $admin_css = '';
        $lines = file('../plugins/' . self::PLUGIN_TITLE. '/admin.css');
        foreach ($lines as $line_num => $line) {
            $admin_css .= trim($line);
        }

        // add template CSS
        $template = '<style>' . $admin_css . '</style>';

        // build Template
        // $template .= '
        //     <div class="gradegen-admin-header">
        //     <span>'
        //         . $this->_admin_lang->getLanguageValue(
        //             'admin_header',
        //             self::PLUGIN_TITLE
        //         )
        //     . '</span>
        //     <a href="' . self::PLUGIN_DOCU . '" target="_blank">
        //     <img style="float:right;" src="' . self::LOGO_URL . '" />
        //     </a>
        //     </div>
        // </li>
        // <li class="mo-in-ul-li ui-widget-content gradegen-admin-li">
        //     <div class="gradegen-admin-subheader">'
        //     . $this->_admin_lang->getLanguageValue('admin_test')
        //     . '</div>
        //     <div class="gradegen-single-conf">
        //         {test1_text}
        //         {test1_description}
        //         <span class="gradegen-admin-default">
        //             [' . /*$this->_confdefault['test1'][0] .*/']
        //         </span>
        //     </div>
        //     <div class="gradegen-single-conf">
        //         {test2_text}
        //         {test2_description}
        //         <span class="gradegen-admin-default">
        //             [' . /*$this->_confdefault['test2'][0] .*/']
        //         </span>
        // ';

        // $config['--template~~'] = $template;

        return $config;
    }

    /**
     * sets default backend configuration elements, if no plugin.conf.php is
     * created yet
     *
     * @return Array configuration
     */
    function getDefaultSettings()
    {
        $config = array('active' => 'true');
        foreach ($this->_confdefault as $elem => $default) {
            $config[$elem] = $default[0];
        }
        return $config;
    }

    /**
     * sets backend plugin information
     *
     * @return Array information
     */
    function getInfo()
    {
        global $ADMIN_CONF;

        $this->_admin_lang = new Language(
            $this->PLUGIN_SELF_DIR
            . 'lang/admin_language_'
            . $ADMIN_CONF->get('language')
            . '.txt'
        );

        // build plugin tags
        $tags = array();
        foreach ($this->_plugin_tags as $key => $tag) {
            $tags[$tag] = $this->_admin_lang->getLanguageValue('tag_' . $key);
        }

        $info = array(
            '<b>' . self::PLUGIN_TITLE . '</b> ' . self::PLUGIN_VERSION,
            self::MOZILO_VERSION,
            $this->_admin_lang->getLanguageValue(
                'description',
                htmlspecialchars($this->_plugin_tags['tag1'])
            ),
            self::PLUGIN_AUTHOR,
            array(
                self::PLUGIN_DOCU,
                self::PLUGIN_TITLE . ' '
                . $this->_admin_lang->getLanguageValue('on_devmount')
            ),
            $tags
        );

        return $info;
    }

    /**
     * creates configuration for text fields
     *
     * @param string $description Label
     * @param string $maxlength   Maximum number of characters
     * @param string $size        Size
     * @param string $regex       Regular expression for allowed input
     * @param string $regex_error Wrong input error message
     *
     * @return Array  Configuration
     */
    protected function confText(
        $description,
        $maxlength = '',
        $size = '',
        $regex = '',
        $regex_error = ''
    ) {
        // required properties
        $conftext = array(
            'type' => 'text',
            'description' => $description,
        );
        // optional properties
        if ($maxlength != '') {
            $conftext['maxlength'] = $maxlength;
        }
        if ($size != '') {
            $conftext['size'] = $size;
        }
        if ($regex != '') {
            $conftext['regex'] = $regex;
        }
        if ($regex_error != '') {
            $conftext['regex_error'] = $regex_error;
        }
        return $conftext;
    }

    /**
     * creates configuration for textareas
     *
     * @param string $description Label
     * @param string $cols        Number of columns
     * @param string $rows        Number of rows
     * @param string $regex       Regular expression for allowed input
     * @param string $regex_error Wrong input error message
     *
     * @return Array  Configuration
     */
    protected function confTextarea(
        $description,
        $cols = '',
        $rows = '',
        $regex = '',
        $regex_error = ''
    ) {
        // required properties
        $conftext = array(
            'type' => 'textarea',
            'description' => $description,
        );
        // optional properties
        if ($cols != '') {
            $conftext['cols'] = $cols;
        }
        if ($rows != '') {
            $conftext['rows'] = $rows;
        }
        if ($regex != '') {
            $conftext['regex'] = $regex;
        }
        if ($regex_error != '') {
            $conftext['regex_error'] = $regex_error;
        }
        return $conftext;
    }

    /**
     * creates configuration for password fields
     *
     * @param string  $description Label
     * @param string  $maxlength   Maximum number of characters
     * @param string  $size        Size
     * @param string  $regex       Regular expression for allowed input
     * @param string  $regex_error Wrong input error message
     * @param boolean $saveasmd5   Safe password as md5 (recommended!)
     *
     * @return Array   Configuration
     */
    protected function confPassword(
        $description,
        $maxlength = '',
        $size = '',
        $regex = '',
        $regex_error = '',
        $saveasmd5 = true
    ) {
        // required properties
        $conftext = array(
            'type' => 'text',
            'description' => $description,
        );
        // optional properties
        if ($maxlength != '') {
            $conftext['maxlength'] = $maxlength;
        }
        if ($size != '') {
            $conftext['size'] = $size;
        }
        if ($regex != '') {
            $conftext['regex'] = $regex;
        }
        $conftext['saveasmd5'] = $saveasmd5;
        return $conftext;
    }

    /**
     * creates configuration for checkboxes
     *
     * @param string $description Label
     *
     * @return Array  Configuration
     */
    protected function confCheck($description)
    {
        // required properties
        return array(
            'type' => 'checkbox',
            'description' => $description,
        );
    }

    /**
     * creates configuration for radio buttons
     *
     * @param string $description  Label
     * @param string $descriptions Array Single item labels
     *
     * @return Array Configuration
     */
    protected function confRadio($description, $descriptions)
    {
        // required properties
        return array(
            'type' => 'select',
            'description' => $description,
            'descriptions' => $descriptions,
        );
    }

    /**
     * creates configuration for select fields
     *
     * @param string  $description  Label
     * @param string  $descriptions Array Single item labels
     * @param boolean $multiple     Enable multiple item selection
     *
     * @return Array   Configuration
     */
    protected function confSelect($description, $descriptions, $multiple = false)
    {
        // required properties
        return array(
            'type' => 'select',
            'description' => $description,
            'descriptions' => $descriptions,
            'multiple' => $multiple,
        );
    }

    /**
     * throws styled message
     *
     * @param string $type Type of message ('ERROR', 'SUCCESS')
     * @param string $text Content of message
     *
     * @return string HTML content
     */
    protected function throwMessage($text, $type)
    {
        return '<div class="'
                . strtolower(self::PLUGIN_TITLE . '-' . $type)
            . '">'
            . '<div>'
                . $this->_cms_lang->getLanguageValue(strtolower($type))
            . '</div>'
            . '<span>' . $text. '</span>'
            . '</div>';
    }

}

?>