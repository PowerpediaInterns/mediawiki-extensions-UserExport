<?php
/**
 * Main file for the UserExport extension.
 */

// Special page class for the UserExport extension.
/**
 * Special page that allows sysops to export the
 * user data to a CSV file.
 *
 * @addtogroup Extensions
 * @author Rodrigo Sampaio Primo <rodrigo@utopia.org.br>
 * @author David Wong
 */
class UserExport extends SpecialPage {
    function __construct() {
        $fieldDefault = [
            'user_id' => false,
            'user_name' => true,
            'user_real_name' => true,
            'user_email' => true,
            'user_registration' => true,
            'user_touched' => false
        ];
        $defaultFields = [];

        $request = $this->getRequest();
        $wpsubmit = $request->getVal('wpsubmit');
        $isUnsubmitted = ($wpsubmit === null);
        foreach ($fieldDefault as $field => $value) {
            $fieldDefault[$field] = ($request->getVal($field) === null && $isUnsubmitted ? $value : $request->getBool($field));
            if ($value) {
                $defaultFields[] = $field;
            }
        }

        $this->fieldDefault = $fieldDefault;
        $this->defaultFields = $defaultFields;

        parent::__construct('UserExport', 'userexport');
    }

    function execute($par) {
        $this->setHeaders();
        $user = $this->getUser();
        if (!$user->isAllowed('userexport')) {
            throw new PermissionsError('userexport');
        }

        $request = $this->getRequest();
        $output = $this->getOutput();
        if ($request->getText('exportusers')) {
            if (!$user->matchEditToken($request->getVal('token'))) {
                // Bad edit token.
                $output->addHtml("<span style=\"color: red;\">" . wfMessage('userexport-badtoken')->escaped() . "</span><br />\n");
            } else {
                $this->exportUsers();
            }
        }

        $html = $this->getPageHeader();
        $output->addHTML($html);
    }

    /**
     * @return string
     */
    function getPageHeader() {
        list($self) = explode('/', $this->getTitle()->getPrefixedDBkey());

        $formDescriptor = [];
        foreach ($this->fieldDefault as $field => $value) {
            $formDescriptor[$field] = [
                'type' => 'check',
                'label' => $field,
                'name' => $field,
                'id' => $field,
                'default' => $value
            ];
        }

        $formDescriptor['submit'] = [
            'class' => HTMLSubmitField::class,
            'buttonlabel-message' => 'userexport-submit',
        ];

        $user = $this->getUser();

        $htmlForm = HTMLForm::factory('ooui', $formDescriptor, $this->getContext());
        $htmlForm
            ->addHiddenField('token', $user->getEditToken())
            ->addHiddenField('exportusers', true)
            ->setWrapperLegendMsg('userexport-formlegend')
            ->addHeaderText(wfMessage('userexport-formdescription')->text(), null)
            ->setAction(Title::newFromText($self)->getLocalURL())
            ->setId('userexport-form')
            ->setFormIdentifier('mw-userexport-form')
            ->suppressDefaultSubmit();

//        $output = $this->getOutput();
//        $output->addHTML(var_export($formDescriptor, true));

        return $htmlForm->prepareForm()->getHTML(true);
    }

    private function getRequestedFields() {
        $request = $this->getRequest();
        $fields = [];
        foreach ($this->fieldDefault as $field => $value) {
            $v = $request->getBool($field);
            if ($v) {
                $fields[] = $field;
            }
        }
        return $fields;
    }

    private function mapUserFields($user, $fields) {
        return array_map(function ($field) use ($user) {
            return $user->$field;
        }, $fields);
    }

    /**
     * Function to query the database and generate the CSV file.
     */
    private function exportUsers() {
        $filePath = tempnam(sys_get_temp_dir(), '');
        $file = fopen($filePath, 'w');
        $fields = $this->getRequestedFields();
        if (count($fields) <= 0) {
            $fields = $this->defaultFields;
        }

        $dbr = wfGetDB(DB_REPLICA);
        $users = $dbr->select('user', $fields);

        fputcsv($file, $fields);

        while ($user = $dbr->fetchObject($users)) {
            fputcsv($file, $this->mapUserFields($user, $fields));
        }

        fclose($file);

        header('Pragma:  no-cache');
        header('Expires: 0');
        header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
        header('Cache-Control: public');
        header('Content-Description: File Transfer');
        header('Content-type: text/csv');
        header('Content-Transfer-Encoding: binary');
        header('Content-Disposition: attachment; filename="mediawiki_users.csv"');
        header('Content-Length: ' . filesize($filePath));
        header('Accept-Ranges: bytes');

        readfile($filePath);
        unlink($filePath);
        die;
    }

    protected function getGroupName() {
        return 'users';
    }
}
