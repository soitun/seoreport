<?php

class IndexController extends Zend_Controller_Action {

    public function init() {
        
    }

    public function indexAction() {
        $this->_redirect('/index/login');
    }

    public function loginAction() {
        $form = new Application_Form_IndexForm();
        $this->view->form = $form;
        if ($form->getValue('check') == TRUE) {
            
        }
        if ($this->_request->isPost() && $form->isValid($_POST)) {
            $data = $form->getValues();
            $db = Zend_Db_Table::getDefaultAdapter();
            $authAdapter = new Zend_Auth_Adapter_DbTable($db, 'user', 'name', 'pass');
            $authAdapter->setIdentity($data['name'])->setCredential($data['pass']);
            setcookie('auth', 'value', time() + 60);
            $auth = Zend_Auth::getInstance();
            $auth->authenticate($authAdapter);
            $storage = $auth->getStorage();
            $userInfo = $authAdapter->getResultRowObject(array('name', 'id', 'account_type', 'status', 'email', 'address', 'logo', 'contact'));
            $storage->write($userInfo);
            $type = $userInfo->account_type;
            $status = $userInfo->status;
            if ($type == 'Admin' && $status == TRUE) {
                $this->_redirect('/admin/index');
            }
            if ($type == 'Client' && $status == TRUE) {
                $this->_redirect('/client/index');
            }
            if ($type == 'Team' && $status == TRUE) {
                $cookie = array(
                    'name' => 'check',
                    'value' => $form->getElement('check'),
                    'expire' => '120',
                    'path' => '/admin/index'
                );
                setcookie($cookie);
                $this->_redirect('/team/index');
            } else {
                echo "<script language='JavaScript' type='text/javascript'>bootbox.alert('Incorrect Username or Password');</script>";
            }
        }
    }

    public function recoverAction() {
        $form = new Application_Form_EmailForm();
        $model = new Application_Model_admin();
        $hash = substr(sha1(microtime()), 0, 6);
        $arr['pass'] = $hash;
        if ($this->_request->isPost() && $form->isValid($_POST)) {
            $data = $form->getValues();
            $id = $data['email'];
            $model->update($arr, "email='$id'");
            $smtpOptions = array('auth' => 'login',
                'username' => 'vermaboni@gmail.com',
                'password' => 'Boni4841@',
                'ssl' => 'ssl',
                'port' => 465);
            $tr = new Zend_Mail_Transport_Smtp('smtp.gmail.com', $smtpOptions);
            Zend_Mail::setDefaultTransport($tr);
            $mail = new Zend_Mail();
            $mail->setBodyText('Your Password has successfully changed.' . ' Your new Account password is ' . "$hash");
            $mail->setFrom('vermaboni@gmail.com', 'Boni Verma');
            $mail->addTo($id, 'fwd');
            $mail->addCc('oditverma@gmail.com', 'fwd');
            $mail->setSubject('TestSubject');
            echo "<script>bootbox.alert('Email Sent on your Mail');</script>";
            $mail->send($tr);
            $this->_redirect('/index');
        }
        $this->view->form = $form;
    }

    public function logoutAction() {
        $authAdapter = Zend_Auth::getInstance();
        $authAdapter->clearIdentity();
        $this->_redirect('/index/login');
    }

}
