<?php

namespace Dharmvijay\LaravelMultiDatabase;

use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

trait BelongsToDatabase
{

    /**
     * @param $user
     */
    public static function connectDynamicUserDb(
        $database_host, 
        $database_port,
        $database_name,
        $database_user,
        $database_password)
    {
        $defaultDbConnection = config('database.default');

        $connectionData = [
            'driver' => config('database.connections.'.$defaultDbConnection.'.driver'),
            'host' => $database_host,
            'port' => $database_port,
            'database' => $database_name,
            'username' => $database_user,
            'password' => self::decryptPasswordString($database_password),
            'charset' => config('database.connections.'.$defaultDbConnection.'.charset'),
            'prefix' => config('database.connections.'.$defaultDbConnection.'.prefix'),
            'schema' => config('database.connections.'.$defaultDbConnection.'.schema'),
            'sslmode' => config('database.connections.'.$defaultDbConnection.'.sslmode'),

        ];
        Config::set($defaultDbConnection, $connectionData);

        try {
            DB::reconnect($defaultDbConnection);
        } catch (\Exception $ex) {
            $message = "User database not found" . $ex->getMessage();
            Log::debug($message);
            throw new \Exception($message);
        }
        return true;
    }

    /**
     * decrpts the password key
     *
     * @param array $user
     *
     * @return mixed
     */
    protected static function decryptPasswordString($database_password)
    {
        try {
            $response = Crypt::decryptString($database_password);
        } catch (DecryptException $e) {
            $response = $database_password;
        }
        return $response;
    }


}
