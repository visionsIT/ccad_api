<?php namespace Modules\Access\Enums;

class UserDataRegistration
{

    /**
     * @return array
     */
    public static function form(): array
    {
        return [
            "first_name" => [
                "is_hidden" => "1",
                "is_mandatory" => "0",
                "is_identifier" => "0",
                "list_of_values" => "0",
                "validation" => [],
                "type" => "text"
            ],
            "last_name" => [
                "is_hidden" => "1",
                "is_mandatory" => "0",
                "is_identifier" => "0",
                "list_of_values" => "0",
                "validation" => [],
                "type" => "text"
            ],
            "password" => [
                "is_hidden" => "1",
                "is_mandatory" => "0",
                "is_identifier" => "0",
                "list_of_values" => "0",
                "validation" => [],
                "type" => "text"
            ],
            "email" => [
                "is_hidden" => "1",
                "is_mandatory" => "0",
                "is_identifier" => "0",
                "list_of_values" => "0",
                "validation" => [],
                "type" => "email"
            ],
            "username" => [
                "is_hidden" => "1",
                "is_mandatory" => "0",
                "is_identifier" => "0",
                "list_of_values" => "0",
                "validation" => [],
                "type" => "text"
            ],
            "title" => [
                "is_hidden" => "1",
                "is_mandatory" => "0",
                "is_identifier" => "0",
                "list_of_values" => "0",
                "validation" => [],
                "type" => "text"
            ],
            "company_name" => [
                "is_hidden" => "1",
                "is_mandatory" => "0",
                "is_identifier" => "0",
                "list_of_values" => [],
                "validation" => [],
                "type" => "select"
            ],
            "job_title" => [
                "is_hidden" => "1",
                "is_mandatory" => "0",
                "is_identifier" => "0",
                "list_of_values" => [],
                "validation" => [],
                "type" => "select"
            ],
            "address_1" => [
                "is_hidden" => "1",
                "is_mandatory" => "0",
                "is_identifier" => "0",
                "list_of_values" => "0",
                "validation" => [],
                "type" => "text"
            ],
            "address_2" => [
                "is_hidden" => "1",
                "is_mandatory" => "0",
                "is_identifier" => "0",
                "list_of_values" => "0",
                "validation" => [],
                "type" => "text"
            ],
            "postcode" => [
                "is_hidden" => "1",
                "is_mandatory" => "0",
                "is_identifier" => "0",
                "list_of_values" => "0",
                "validation" => [],
                "type" => "text"
            ],
            "country" => [
                "is_hidden" => "1",
                "is_mandatory" => "0",
                "is_identifier" => "0",
                "list_of_values" => [], // list of all countries ( add ids )
                "validation" => [],
                "type" => "select"
            ],
            "telephone" => [
                "is_hidden" => "1",
                "is_mandatory" => "0",
                "is_identifier" => "0",
                "list_of_values" => "0",
                "validation" => [],
                "type" => "number"
            ],
            "mobile" => [
                "is_hidden" => "1",
                "is_mandatory" => "0",
                "is_identifier" => "0",
                "list_of_values" => "0",
                "validation" => [],
                "type" => "number"
            ],
            "date_of_birth" => [
                "is_hidden" => "1",
                "is_mandatory" => "0",
                "is_identifier" => "0",
                "list_of_values" => "0",
                "validation" => [],
                "type" => "date"
            ],
            "communication_preference" => [
                "is_hidden" => "1",
                "is_mandatory" => "0",
                "is_identifier" => "0",
                "list_of_values" => [],
                "validation" => [],
                "type" => "radio_button"
            ],
            "language" => [
                "is_hidden" => "1",
                "is_mandatory" => "0",
                "is_identifier" => "0",
                "list_of_values" => [],
                "validation" => [],
                "type" => "radio_button"
            ],
        ];
    }

    /**
     * @return array
     */
    public static function userIdentifier(): array
    {
        return [
            "Username",
            "Email"
        ];
    }

    /**
     * @return array
     */
    public static function optionalFields(): array
    {
        return [
            "Title",
            "Company Name",
            "Job Title",
            "Address fields",
            "Postcode",
            "Country",
            "Telephone",
            "Mobile telephone",
            "Date of birth",
            "Communication preference",
            "Language"
        ];
    }
}
