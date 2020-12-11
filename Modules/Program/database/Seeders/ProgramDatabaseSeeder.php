<?php namespace Modules\Program\Database\Seeders;
use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Modules\Program\Models\Program;
use Spatie\Permission\Models\Role;

class ProgramDatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run(): void
    {
        Model::unguard();
        $this->insertCurrenciesInDb();
        factory(Program::class, 5)->create();
        $this->generateUserGroupForSeededProgram(Program::all());
    }

    private function insertCurrenciesInDb(): void
    {
        if (!DB::table('currencies')->count()) {
            DB::table('currencies')->insert([
                [ 'name' => 'United Arab Emirates Dirham' ],
                [ 'name' => 'Afghan Afghani' ],
                [ 'name' => 'Albanian Lek' ],
                [ 'name' => 'Armenian Dram' ],
                [ 'name' => 'Netherlands Antillean Guilder' ],
                [ 'name' => 'Angolan Kwanza' ],
                [ 'name' => 'Argentine Peso' ],
                [ 'name' => 'Australian Dollar' ],
                [ 'name' => 'Aruban Florin' ],
                [ 'name' => 'Azerbaijani Manat' ],
                [ 'name' => 'Bosnia-Herzegovina Convertible Mark' ],
                [ 'name' => 'Barbadian Dollar' ],
                [ 'name' => 'Bangladeshi Taka' ],
                [ 'name' => 'Bulgarian Lev' ],
                [ 'name' => 'Bahraini Dinar' ],
                [ 'name' => 'Burundian Franc' ],
                [ 'name' => 'Bermudan Dollar' ],
                [ 'name' => 'Brunei Dollar' ],
                [ 'name' => 'Bolivian Boliviano' ],
                [ 'name' => 'Brazilian Real' ],
                [ 'name' => 'Bahamian Dollar' ],
                [ 'name' => 'Bitcoin' ],
                [ 'name' => 'Bhutanese Ngultrum' ],
                [ 'name' => 'Botswanan Pula' ],
                [ 'name' => 'Belarusian Ruble' ],
                [ 'name' => 'Belize Dollar' ],
                [ 'name' => 'Canadian Dollar' ],
                [ 'name' => 'Congolese Franc' ],
                [ 'name' => 'Swiss Franc' ],
                [ 'name' => 'Chilean Unit of Account (UF)' ],
                [ 'name' => 'Chilean Peso' ],
                [ 'name' => 'Chinese Yuan (Offshore)' ],
                [ 'name' => 'Chinese Yuan' ],
                [ 'name' => 'Colombian Peso' ],
                [ 'name' => 'Costa Rican Colón' ],
                [ 'name' => 'Cuban Convertible Peso' ],
                [ 'name' => 'Cuban Peso' ],
                [ 'name' => 'Cape Verdean Escudo' ],
                [ 'name' => 'Czech Republic Koruna' ],
                [ 'name' => 'Djiboutian Franc' ],
                [ 'name' => 'Danish Krone' ],
                [ 'name' => 'Dominican Peso' ],
                [ 'name' => 'Algerian Dinar' ],
                [ 'name' => 'Egyptian Pound' ],
                [ 'name' => 'Eritrean Nakfa' ],
                [ 'name' => 'Ethiopian Birr' ],
                [ 'name' => 'Euro' ],
                [ 'name' => 'Fijian Dollar' ],
                [ 'name' => 'Falkland Islands Pound' ],
                [ 'name' => 'British Pound Sterling' ],
                [ 'name' => 'Georgian Lari' ],
                [ 'name' => 'Guernsey Pound' ],
                [ 'name' => 'Ghanaian Cedi' ],
                [ 'name' => 'Gibraltar Pound' ],
                [ 'name' => 'Gambian Dalasi' ],
                [ 'name' => 'Guinean Franc' ],
                [ 'name' => 'Guatemalan Quetzal' ],
                [ 'name' => 'Guyanaese Dollar' ],
                [ 'name' => 'Hong Kong Dollar' ],
                [ 'name' => 'Honduran Lempira' ],
                [ 'name' => 'Croatian Kuna' ],
                [ 'name' => 'Haitian Gourde' ],
                [ 'name' => 'Hungarian Forint' ],
                [ 'name' => 'Indonesian Rupiah' ],
                [ 'name' => 'Israeli New Sheqel' ],
                [ 'name' => 'Manx pound' ],
                [ 'name' => 'Indian Rupee' ],
                [ 'name' => 'Iraqi Dinar' ],
                [ 'name' => 'Iranian Rial' ],
                [ 'name' => 'Icelandic Króna' ],
                [ 'name' => 'Jersey Pound' ],
                [ 'name' => 'Jamaican Dollar' ],
                [ 'name' => 'Jordanian Dinar' ],
                [ 'name' => 'Japanese Yen' ],
                [ 'name' => 'Kenyan Shilling' ],
                [ 'name' => 'Kyrgystani Som' ],
                [ 'name' => 'Cambodian Riel' ],
                [ 'name' => 'Comorian Franc' ],
                [ 'name' => 'North Korean Won' ],
                [ 'name' => 'South Korean Won' ],
                [ 'name' => 'Kuwaiti Dinar' ],
                [ 'name' => 'Cayman Islands Dollar' ],
                [ 'name' => 'Kazakhstani Tenge' ],
                [ 'name' => 'Laotian Kip' ],
                [ 'name' => 'Lebanese Pound' ],
                [ 'name' => 'Sri Lankan Rupee' ],
                [ 'name' => 'Liberian Dollar' ],
                [ 'name' => 'Lesotho Loti' ],
                [ 'name' => 'Libyan Dinar' ],
                [ 'name' => 'Moroccan Dirham' ],
                [ 'name' => 'Moldovan Leu' ],
                [ 'name' => 'Malagasy Ariary' ],
                [ 'name' => 'Macedonian Denar' ],
                [ 'name' => 'Myanma Kyat' ],
                [ 'name' => 'Mongolian Tugrik' ],
                [ 'name' => 'Macanese Pataca' ],
                [ 'name' => 'Mauritanian Ouguiya (pre-2018)' ],
                [ 'name' => 'Mauritanian Ouguiya' ],
                [ 'name' => 'Mauritian Rupee' ],
                [ 'name' => 'Maldivian Rufiyaa' ],
                [ 'name' => 'Malawian Kwacha' ],
                [ 'name' => 'Mexican Peso' ],
                [ 'name' => 'Malaysian Ringgit' ],
                [ 'name' => 'Mozambican Metical' ],
                [ 'name' => 'Namibian Dollar' ],
                [ 'name' => 'Nigerian Naira' ],
                [ 'name' => 'Nicaraguan Córdoba' ],
                [ 'name' => 'Norwegian Krone' ],
                [ 'name' => 'Nepalese Rupee' ],
                [ 'name' => 'New Zealand Dollar' ],
                [ 'name' => 'Omani Rial' ],
                [ 'name' => 'Panamanian Balboa' ],
                [ 'name' => 'Peruvian Nuevo Sol' ],
                [ 'name' => 'Papua New Guinean Kina' ],
                [ 'name' => 'Philippine Peso' ],
                [ 'name' => 'Pakistani Rupee' ],
                [ 'name' => 'Polish Zloty' ],
                [ 'name' => 'Paraguayan Guarani' ],
                [ 'name' => 'Qatari Rial' ],
                [ 'name' => 'Romanian Leu' ],
                [ 'name' => 'Serbian Dinar' ],
                [ 'name' => 'Russian Ruble' ],
                [ 'name' => 'Rwandan Franc' ],
                [ 'name' => 'Saudi Riyal' ],
                [ 'name' => 'Solomon Islands Dollar' ],
                [ 'name' => 'Seychellois Rupee' ],
                [ 'name' => 'Sudanese Pound' ],
                [ 'name' => 'Swedish Krona' ],
                [ 'name' => 'Singapore Dollar' ],
                [ 'name' => 'Saint Helena Pound' ],
                [ 'name' => 'Sierra Leonean Leone' ],
                [ 'name' => 'Somali Shilling' ],
                [ 'name' => 'Surinamese Dollar' ],
                [ 'name' => 'South Sudanese Pound' ],
                [ 'name' => 'São Tomé and Príncipe Dobra (pre-2018)' ],
                [ 'name' => 'São Tomé and Príncipe Dobra' ],
                [ 'name' => 'Salvadoran Colón' ],
                [ 'name' => 'Syrian Pound' ],
                [ 'name' => 'Swazi Lilangeni' ],
                [ 'name' => 'Thai Baht' ],
                [ 'name' => 'Tajikistani Somoni' ],
                [ 'name' => 'Turkmenistani Manat' ],
                [ 'name' => 'Tunisian Dinar' ],
                [ 'name' => 'Tongan Pa\'anga' ],
                [ 'name' => 'Turkish Lira' ],
                [ 'name' => 'Trinidad and Tobago Dollar' ],
                [ 'name' => 'New Taiwan Dollar' ],
                [ 'name' => 'Tanzanian Shilling' ],
                [ 'name' => 'Ukrainian Hryvnia' ],
                [ 'name' => 'Ugandan Shilling' ],
                [ 'name' => 'United States Dollar' ],
                [ 'name' => 'Uruguayan Peso' ],
                [ 'name' => 'Uzbekistan Som' ],
                [ 'name' => 'Venezuelan Bolívar Fuerte (Old)' ],
                [ 'name' => 'Venezuelan Bolívar Soberano' ],
                [ 'name' => 'Vietnamese Dong' ],
                [ 'name' => 'Vanuatu Vatu' ],
                [ 'name' => 'Samoan Tala' ],
                [ 'name' => 'CFA Franc BEAC' ],
                [ 'name' => 'Silver Ounce' ],
                [ 'name' => 'Gold Ounce' ],
                [ 'name' => 'East Caribbean Dollar' ],
                [ 'name' => 'Special Drawing Rights' ],
                [ 'name' => 'CFA Franc BCEAO' ],
                [ 'name' => 'Palladium Ounce' ],
                [ 'name' => 'CFP Franc' ],
                [ 'name' => 'Platinum Ounce' ],
                [ 'name' => 'Yemeni Rial' ],
                [ 'name' => 'South African Rand' ],
                [ 'name' => 'Zambian Kwacha' ],
                [ 'name' => 'Zimbabwean Dollar' ],
            ]);
        }
    }

    private function generateUserGroupForSeededProgram($programs)
    {

        foreach ($programs as $program)
        {
            Role::create([
               'name' => $program->name,
               'program_id' => $program->id
            ]);
        }
    }



}
