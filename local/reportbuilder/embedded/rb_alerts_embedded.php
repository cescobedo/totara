<?php

class rb_alerts_embedded extends rb_base_embedded {

    public $url, $source, $fullname, $filters, $columns;
    public $contentmode, $contentsettings, $embeddedparams;
    public $hidden, $accessmode, $accesssettings, $shortname;

    public function __construct($data) {
        $userid = array_key_exists('userid', $data) ? $data['userid'] : null;
        $roleid = array_key_exists('roleid', $data) ? $data['roleid'] : null;

        $this->url = '/local/totara_msg/alerts.php';
        $this->source = 'totaramessages';
        $this->shortname = 'alerts';
        $this->fullname = get_string('alerts', 'local_totara_msg');
        $this->columns = array(
//            array(
//                'type' => 'message_values',
//                'value' => 'urgency',
//                'heading' => 'Urgency',
//            ),
            array(
                'type' => 'message_values',
                'value' => 'msgtype',
                'heading' => 'Type',
            ),
            array(
                'type' => 'user',
                'value' => 'namelink',
                'heading' => 'Name',
            ),
            array(
                'type' => 'message_values',
                'value' => 'statement',
                'heading' => 'Details',
            ),
            array(
                'type' => 'message_values',
                'value' => 'sent',
                'heading' => 'Sent',
            ),
            array(
                'type' => 'message_values',
                'value' => 'dismiss_link',
                'heading' =>
                '<div id="totara_msg_selects" style="display: none;">'.
                '<a id="all">'.get_string('all').'</a>/'.
                '<a id="none">'.get_string('none').'</a>'.
                '</div><noscript>'.get_string('actions').'</noscript>',
                ),
        );

        $this->filters = array(
            array(
                    'type' => 'user',
                    'value' => 'fullname',
                    'advanced' => 1,
                ),
            array(
                    'type' => 'message_values',
                    'value' => 'category',
                    'advanced' => 0,
                ),
    //        array(
    //                'type' => 'message_values',
    //                'value' => 'msgstatus',
    //                'advanced' => 1,
    //            ),
//            array(
//                    'type' => 'message_values',
//                    'value' => 'urgency',
//                    'advanced' => 1,
//                ),
            array(
                    'type' => 'message_values',
                    'value' => 'statement',
                    'advanced' => 1,
                ),
            array(
                    'type' => 'message_values',
                    'value' => 'sent',
                    'advanced' => 1,
                ),
        );

        // no restrictions
        $this->contentmode = REPORT_BUILDER_CONTENT_MODE_NONE;

        // only show alerts, not tasks
        $this->embeddedparams = array(
            'name' => '\'totara_alert\''
        );
        // also limited to single user
        if(isset($userid)) {
            $this->embeddedparams['userid'] = $userid;
        }
        // also limited by role
        if(isset($roleid)) {
            $this->embeddedparams['roleid'] = $roleid;
        }

        parent::__construct();
    }
}