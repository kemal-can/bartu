<?php
/**
 * Bartu CRM - https://github.com/kemal-can/BARTU-Comprehensive-CRM
 *
 * @version   1.1.7
 *
 * @link      Releases - https://github.com/kemal-can/BARTU-Comprehensive-CRM
 *
 * @copyright Copyright (c) 2019-2022 mail@kemalcan.net
 */

namespace App\Enums;

enum WebFormSection : string {
    case FILE         = 'file-section';
    case FIELD        = 'field-section';
    case SUBMIT       = 'submit-button-section';
    case MESSAGE      = 'message-section';
    case INTRODUCTION = 'introduction-section';
}
