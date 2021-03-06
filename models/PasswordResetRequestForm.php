<?php
namespace app\models;

use yii\base\Model;
use Yii;

/**
 * Password reset request form.
 */
class PasswordResetRequestForm extends Model
{
    public $email;

    /**
     * Returns the validation rules for attributes.
     *
     * @return array
     */
    public function rules()
    {
        return [
            ['email', 'filter', 'filter' => 'trim'],
            ['email', 'required'],
            ['email', 'email'],
            ['email', 'exist',
                'targetClass' => '\app\models\User',
                'filter' => ['status' => User::STATUS_ACTIVE],
                'message' => 'Wrong email.'
            ],
        ];
    }
   
    public function sendEmail()
    {        
        $user = User::findOne(['status' => User::STATUS_ACTIVE, 'email' => $this->email]);

        if (!$user) {
            return false;
        }

        if (!User::isPasswordResetTokenValid($user->password_reset_token)) {
            $user->generatePasswordResetToken();
        }

        if (!$user->save()) {
            return false;
        }

        return Yii::$app->mailer->compose('passwordResetToken', ['user' => $user])
                                ->setFrom([Yii::$app->params['supportEmail'] => Yii::$app->name . ' robot'])
                                ->setTo($this->email)
                                ->setSubject('Password reset for ' . Yii::$app->name)
                                ->send();
    }
}
