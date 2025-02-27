<?php

namespace Core;

final class Session
{
    /**
     * Inicia uma sessão.
     *
     * @param string $nome Nome da variável de sessão.
     * @param mixed $valor Valor da variável de sessão.
     */
    public static function start(?string $cacheExpire = null, ?string $cacheLimiter = null){
        if (session_status() === PHP_SESSION_NONE) {

            if ($cacheLimiter !== null) {
                session_cache_limiter($cacheLimiter);
            }

            if ($cacheExpire !== null) {
                session_cache_expire($cacheExpire);
            }

            session_set_cookie_params([
                'httponly' => true
            ]);

            session_start();
        }
    }

    public static function getId(){
        return \session_id();
    }

    public static function destroy(){
        return session_destroy();
    }

    /**
     * Define uma variável de sessão.
     *
     * @param string $nome Nome da variável de sessão.
     * @param mixed $valor Valor da variável de sessão.
     */
    public static function set(string $nome, $valor):void
    {
        $_SESSION["_".$nome] = $valor;
    }

    /**
     * Obtém o valor de uma variável de sessão.
     *
     * @param string $nome Nome da variável de sessão.
     * @return mixed Valor da variável de sessão ou uma string vazia se não existir.
     */
    public static function get(string $nome):mixed
    {
        return array_key_exists("_".$nome, $_SESSION) ? $_SESSION["_".$nome] : null;
    }
}