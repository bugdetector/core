<?php

namespace CoreDB\Kernel;

use CoreDB\Kernel\Database\DataType\Checkbox;
use CoreDB\Kernel\Database\DataType\DataTypeAbstract;
use CoreDB\Kernel\Database\DataType\Date;
use CoreDB\Kernel\Database\DataType\DateTime;
use CoreDB\Kernel\Database\DataType\File;
use CoreDB\Kernel\Database\DataType\TableReference;
use CoreDB\Kernel\Database\DataType\Time;
use CoreDB\Kernel\Model;

abstract class EncryptedModel extends Model
{
    protected const ENCRYPTION_ALG = "AES128";

    /**
     * Set fields of object using an array with same keys
     * @param array $array
     *  Containing field values to set
     */
    public function map(array $array, bool $isConstructor = false)
    {
        if ($isConstructor) {
            foreach ($array as $key => &$value) {
                if (
                    $key != "ID" &&
                    !($this->{$key} instanceof EntityReference) &&
                    !($this->{$key} instanceof TableReference) &&
                    !($this->{$key} instanceof File) &&
                    !($this->{$key} instanceof Checkbox) &&
                    !($this->{$key} instanceof DateTime) &&
                    !($this->{$key} instanceof Date) &&
                    !($this->{$key} instanceof Time)
                ) {
                    $value = $this->decrypt($value);
                }
            }
        }
        parent::map($array);
    }

     /**
     * Converts an object to array including private fields
     * @return \array
     */
    public function toArray(): array
    {
        foreach ($this as $field_name => $field) {
            if (
                !($field instanceof DataTypeAbstract) ||
                ($field instanceof EntityReference) ||
                in_array($field_name, ["ID", "created_at", "last_updated", "changed_fields"])
            ) {
                continue;
            }
            if (
                $field_name != "ID" &&
                !($field instanceof TableReference) &&
                !($field instanceof File) &&
                !($field instanceof Checkbox) &&
                !($field instanceof DateTime) &&
                !($field instanceof Date) &&
                !($field instanceof Time)
            ) {
                $object_as_array[$field_name] = $this->encrypt($field->getValue());
            } else {
                $object_as_array[$field_name] = $field->getValue();
            }
        }
        return $object_as_array;
    }

    protected function getIV()
    {
        return substr(hash("SHA256", static::class), 0, 16);
    }

     /**
     *
     * @return string
     */
    private function encrypt($pure_string)
    {
        $encrypted_string = openssl_encrypt(
            $pure_string,
            self::ENCRYPTION_ALG,
            HASH_SALT,
            OPENSSL_RAW_DATA,
            $this->getIV()
        );
        return base64_encode($encrypted_string);
    }

    /**
     *
     * @return type
     */
    private function decrypt($encrypted_string)
    {
        $encrypted_string = base64_decode($encrypted_string);
        $decrypted_string = openssl_decrypt(
            $encrypted_string,
            self::ENCRYPTION_ALG,
            HASH_SALT,
            OPENSSL_RAW_DATA,
            $this->getIV()
        );
        return $decrypted_string;
    }
}
