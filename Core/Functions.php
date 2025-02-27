<?php

namespace Core;

use Core\Logger;
use NumberFormatter;

abstract class Functions
{
    private function __construct()
    {
    }
    /**
     * Retorna o diretório raiz do servidor
     *
     * @return string O diretório raiz do servidor
     */
    public static function getRoot(): string
    {
        return dirname(__DIR__)."/";
    }
    
    /**
     * Decodifica uma string UTF-8 codificada para URL
     *
     * @param string $str A string codificada para URL
     * @return string A string decodificada
     */
    public static function utf8_urldecode(string $str):string
    {
        return mb_convert_encoding(preg_replace("/%u([0-9a-f]{3,4})/i", "&#x\\1;", urldecode($str)),'UTF-8');
    }
    
    /**
     * Remove todos os caracteres não numéricos de uma string
     *
     * @param string $value A string a ser filtrada
     * @return string A string contendo apenas números
     */
    public static function onlynumber(string $value):string
    {
        $value = preg_replace("/[^0-9]/","", $value);
        return $value;
    }

    /**
     * Converte uma string para o formato de data e hora do banco de dados
     *
     * @param string $string A string contendo a data e hora
     * @return string|bool A string formatada ou false se falhar
     */
    public static function dateTimeBd(null|string $string):string|bool
    {
        if(!$string){
            return false;
        }

        $string = str_replace("/","-",$string);
        $datetime = new \DateTimeImmutable($string);
        if ($datetime !== false)
            return $datetime->format('Y-m-d H:i:s');

        return false;
    }

    /**
     * Converte uma string para o formato de data e hora BR
     *
     * @param string $string A string contendo a data e hora
     * @return string|bool A string formatada ou false se falhar
     */
    public static function dateTimeBr(string $string):string|bool
    {
        if(!$string){
            return false;
        }
        
        $string = str_replace("/","-",$string);
        $datetime = new \DateTimeImmutable($string);
        if ($datetime !== false)
            return $datetime->format('d/m/Y H:i:s');

        return false;
    }

    /**
     * Validada se uma cor é valida
     *
     * @param string $string A string contendo a data
     * @return string|bool A string formatada ou false se falhar
     */
    public static function validColor(string $cor):string|bool
    {
        // Expressão regular para verificar cor hexadecimal
        $padrao_hex = '/^#([A-Fa-f0-9]{6}|[A-Fa-f0-9]{3})$/';
        
        // Expressão regular para verificar cor RGB
        $padrao_rgb = '/^rgb\(\s*(\d{1,3})\s*,\s*(\d{1,3})\s*,\s*(\d{1,3})\s*\)$/';
        
        // Verificar se a cor é hexadecimal ou RGB
        if (preg_match($padrao_hex, $cor) || preg_match($padrao_rgb, $cor)) {
            return $cor;
        } else {
            return false;
        }
    }

    /**
     * Converte uma string para o formato de data do banco de dados
     *
     * @param string $string A string contendo a data
     * @return string|bool A string formatada ou false se falhar
     */
    public static function dateBd(string $string):string|bool
    {
        $datetime = new \DateTimeImmutable($string);
        if ($datetime !== false)
            return $datetime->format('Y-m-d');

        return false;
    }

    public static function validBase64(string $s)
    {
          return (bool) preg_match('/^[a-zA-Z0-9\/\r\n+]*={0,2}$/', $s);
    }

     /**
     * Converte uma string para o formato de data BR
     *
     * @param string $string A string contendo a data
     * @return string|bool A string formatada ou false se falhar
     */
    public static function dateBr(string $string):string|bool
    {
        $datetime = new \DateTimeImmutable($string);
        if ($datetime !== false)
            return $datetime->format('d/m/Y');

        return false;
    }

    public static function validCpfCnpj($cpf_cnpj):bool
    {
        $cpf_cnpj = preg_replace('/[^0-9]/', '', (string)$cpf_cnpj);

        if (strlen($cpf_cnpj) == 14)
            return self::validCnpj($cpf_cnpj);
        elseif(strlen($cpf_cnpj) == 11)
            return self::validCpf($cpf_cnpj);
        else 
            return false;
    }

    /**
     * Valida se o cnpj é valido
     *
     * @param string $cnpj A string contendo a data
     * @return bool Se for validado true senão fals
    */
    public static function validCnpj($cnpj):bool
    {
        $cnpj = preg_replace('/[^0-9]/', '', (string)$cnpj);

        // Valida tamanho
        if (strlen($cnpj) != 14)
            return false;

        // Verifica se todos os digitos são iguais
        if (preg_match('/(\d)\1{13}/', $cnpj))
            return false;	

        // Valida primeiro dígito verificador
        for ($i = 0, $j = 5, $soma = 0; $i < 12; $i++)
        {
            $soma += $cnpj[$i] * $j;
            $j = ($j == 2) ? 9 : $j - 1;
        }

        $resto = $soma % 11;

        if ($cnpj[12] != ($resto < 2 ? 0 : 11 - $resto))
            return false;

        // Valida segundo dígito verificador
        for ($i = 0, $j = 6, $soma = 0; $i < 13; $i++)
        {
            $soma += $cnpj[$i] * $j;
            $j = ($j == 2) ? 9 : $j - 1;
        }

        $resto = $soma % 11;

        return $cnpj[13] == ($resto < 2 ? 0 : 11 - $resto);
    }

    /**
     * Valida se o cpf é valido
     *
     * @param string $cnpj A string contendo a data
     * @return bool Se for validado true senão false
    */
    public static function validCpf($cpf):bool
    {
        // Extrai somente os números
        $cpf = preg_replace( '/[^0-9]/', '', (string)$cpf);
        
        // Verifica se foi informado todos os digitos corretamente
        if (strlen($cpf) != 11) {
            return false;
        }

        // Verifica se foi informada uma sequência de digitos repetidos. Ex: 111.111.111-11
        if (preg_match('/(\d)\1{10}/', $cpf)) {
            return false;
        }

        // Faz o calculo para validar o CPF
        for ($t = 9; $t < 11; $t++) {
            for ($d = 0, $c = 0; $c < $t; $c++) {
                $d += $cpf[$c] * (($t + 1) - $c);
            }
            $d = ((10 * $d) % 11) % 10;
            if ($cpf[$c] != $d) {
                return false;
            }
        }

        return true;
    }

    /**
     * Formata um CNPJ ou CPF
     *
     * @param string $value O valor do CNPJ ou CPF
     * @return string O valor formatado
     */
    public static function formatCnpjCpf(?string $value):string|bool
    {
        if(!$value){
            return false;
        }

        $CPF_LENGTH = 11;
        $cnpj_cpf = preg_replace("/\D/", '', $value);

        if (strlen($cnpj_cpf) === $CPF_LENGTH) {
            return Functions::mask($cnpj_cpf, '###.###.###-##');
        } 
        
        return Functions::mask($cnpj_cpf, '##.###.###/####-##');
    }

    /**
     * Aplica uma máscara a uma string
     *
     * @param string $val A string original
     * @param string $mask A máscara a ser aplicada
     * @return string A string com a máscara aplicada
     */
    public static function mask(string $val,string $mask):string
    {
        $maskared = '';
        $k = 0;
        for($i = 0; $i<=strlen($mask)-1; $i++) {
            if($mask[$i] == '#') {
                if(isset($val[$k])) $maskared .= $val[$k++];
            } else {
                if(isset($mask[$i])) $maskared .= $mask[$i];
            }
        }
        return $maskared;
    }


    /**
     * Valida se uma email é valido
     *
     * @param string $email A string com o email
     * @return bool se é valido
    */
    public static function validEmail($email):bool
    {
        return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
    }

    /**
     * Valida se os dias são validos
     *
     * @param string $dias A string com os dias
     * @return bool se é valido
    */
    public static function validarDiasSemana($dias_semana):bool
    {
        
        // Dividir a lista em dias individuais
        $dias = explode(",", $dias_semana);
        
        if(count($dias) <= 7){
            // Verificar cada dia individualmente
            foreach ($dias as $dia) {
                $dia = trim($dia);
                if (!in_array($dia, ["","dom", "seg", "ter", "qua", "qui", "sex", "sab"])) {
                    return false; // Dia inválido encontrado
                }
            }
            
            return true; // Todos os dias são válidos
        }

        return false;
    }

    /**
     * Valida se uma horario é valido
     *
     * @param string $horario A string com o horario
     * @return bool se é valido
    */
    public static function validaHour(string $horario):bool
    {
        // Expressão regular para validar o formato HH:MM:SS
        $padrao_horario = '/^([01]?[0-9]|2[0-3]):([0-5]?[0-9]):([0-5]?[0-9])$/';
        
        // Verificar se o horário corresponde ao padrão
        if (preg_match($padrao_horario, $horario, $matches)) {
            // Verificar se os valores de hora, minuto e segundo estão dentro dos limites corretos
            $hora = intval($matches[1]);
            $minuto = intval($matches[2]);
            $segundo = intval($matches[3]);
            
            if ($hora >= 0 && $hora <= 23 && $minuto >= 0 && $minuto <= 59 && $segundo >= 0 && $segundo <= 59) {
                return true;
            }
        }
        
        return false;
    }

    /**
     * Formata uma string de tempo para o formato HH:MM:SS ou HH:MM
     *
     * @param string $time A string de tempo a ser formatada
     * @return string A string de tempo formatada
     */
    public static function formatTime(string $time):string
    {
        if ($tamanho = substr_count($time,":")){
            if ($tamanho == 2){
                return $time;
            }
            if ($tamanho == 1){
                return $time.":00";
            }
        }
        else{
            return $time.":00:00";
        }
    }

    /**
     * Multiplica um tempo pela quantidade informada
     *
     * @param string $tempo A string de tempo a ser modificada
     * @return int A string de tempo sem os segundos
    */
    public static function multiplicarTempo(string $tempo,int $quantidade):string
    {
        // Divide o tempo em horas, minutos e segundos
        list($horas, $minutos, $segundos) = explode(':', $tempo);
    
        // Converte tudo para segundos
        $totalSegundos = $horas * 3600 + $minutos * 60 + $segundos;
    
        // Multiplica pelos segundos pela quantidade especificada
        $totalSegundos *= $quantidade;
    
        // Calcula as novas horas, minutos e segundos
        $novasHoras = floor($totalSegundos / 3600);
        $totalSegundos %= 3600;
        $novosMinutos = floor($totalSegundos / 60);
        $novosSegundos = $totalSegundos % 60;
    
        // Formata a nova hora no formato HH:MM:SS
        return sprintf('%02d:%02d:%02d', $novasHoras, $novosMinutos, $novosSegundos);
    }

    /**
     * Remove os segundos de uma string de tempo
     *
     * @param string $time A string de tempo a ser modificada
     * @return string A string de tempo sem os segundos
     */
    public static function removeSecondsTime($time):string
    {
        if ($tamanho = substr_count($time,":")){
            if ($tamanho == 2){
                $time = explode(":",$time);
                return $time[0].":".$time[1];
            }
            if ($tamanho == 1){
                return $time;
            }
        }
        else{
            return $time.":00";
        }
    }

    /**
     * Formata uma string contendo dias, substituindo vírgulas por espaços
     *
     * @param string $dias A string contendo os dias
     * @return string A string formatada
     */
    public static function formatDays($dias):string
    {
        $dias = str_replace(","," ",$dias);
        $dias = trim($dias);

        return $dias;
    }   

    /**
     * Formata um valor monetário para o formato de moeda brasileira
     *
     * @param string $input O valor monetário
     * @return string O valor monetário formatado
     */
    public static function formatCurrency(float|int $input):string
    {
        $fmt = new NumberFormatter('pt-BR', NumberFormatter::CURRENCY );
        return $fmt->format($input);
    }

    /**
     * Remove a formatação de moeda e retorna um valor numérico
     *
     * @param string $input O valor monetário formatado
     * @return float O valor numérico
     */
    public static function removeCurrency($input):string
    {
        return floatval(str_replace(",",".",preg_replace("/[^0-9.,]/", "", $input)));
    }

    /**
     * Gera um código aleatório baseado em bytes randômicos
     *
     * @param int $number O número de bytes para gerar o código
     * @return string O código gerado
     */
    public static function genereteRandomNumber($number):string
    {
        return substr(strtoupper(substr(bin2hex(random_bytes($number)), 1)),0,$number);
    }

    /**
     * Formata um endereço IP para o formato XXX.XXX.XXX.XXX
     *
     * @param string $ip O endereço IP a ser formatado
     * @return string|bool O endereço IP formatado ou false se inválido
     */
    public static function formatIP($ip):string
    {

        $ip = preg_replace('/\D/', '', $ip);

        $tamanho = strlen($ip);

        // Validar se o IP possui 12 dígitos
        if ($tamanho < 4 || $tamanho > 12) {
            // Se não tiver 12 dígitos, retorne false
            return false;
        }

        // Formatar o IP no formato desejado (XXX.XXX.XXX.XXX)
        return sprintf("%03d.%03d.%03d.%03d", substr($ip, 0, 3), substr($ip, 3, 3), substr($ip, 6, 3), substr($ip, 9, 3));

    }

    /**
     * Valida um número de cep para o formato XXXXX ou XXXXX-XXX
     *
     * @param string $cep O número de cep a ser validadp
     * @return bool O número de cep valido ou false se inválido
    */
    public static function validCep($cep):bool 
    {
        if(preg_match('/^[0-9]{5,5}([- ]?[0-9]{3,3})?$/', $cep)) {
            return true;
        }
        return false;
    }

    /**
     * Valida um número de telefone para o formato (XX) XXXX-XXXX ou (XX) XXXXX-XXXX
     *
     * @param string $telefone O número de telefone a ser validadp
     * @return bool O número de telefone valido ou false se inválido
     */
public static function validPhone($telefone):bool 
    {
        // Remover quaisquer caracteres que não sejam dígitos
        $telefone = preg_replace('/\D/', '', $telefone);
            
        // Verificar se o número de telefone tem o comprimento correto
        if (strlen($telefone) != 10 && strlen($telefone) != 11) {
            return false; // Retornar falso se o comprimento for inválido
        }

        return true;
    }

    public static function formatPhone($telefone):string|bool
    {
        if(!$telefone)
            return false;

        $telefone = self::onlynumber($telefone);

        if(strlen($telefone) == 10)
            return '(' . substr($telefone, 0, 2) . ') ' . substr($telefone, 2, 4) . '-' . substr($telefone, 6);
        else 
            return '(' . substr($telefone, 0, 2) . ') ' . substr($telefone, 2, 5) . '-' . substr($telefone, 7);
    }

    public static function formatCep($cep):string|bool
    {
        if(!$cep)
            return false;

        $cep = self::onlynumber($cep);

        if(strlen($cep) == 8)
            return substr($cep, 0, 5) . '-' . substr($cep, 5, 3);

        return false;
    }

    public static function removeAcentos(string $string){
        return preg_replace(array("/(á|à|ã|â|ä)/","/(Á|À|Ã|Â|Ä)/","/(é|è|ê|ë)/","/(É|È|Ê|Ë)/","/(í|ì|î|ï)/","/(Í|Ì|Î|Ï)/","/(ó|ò|õ|ô|ö)/","/(Ó|Ò|Õ|Ô|Ö)/","/(ú|ù|û|ü)/","/(Ú|Ù|Û|Ü)/","/(ñ)/","/(Ñ)/"),explode(" ","a A e E i I o O u U n N"),$string);
    }

    public static function createNameId(string $string){
        return str_replace("[]","",str_replace(" ","-",self::removeAcentos(strtolower($string))));
    }

    public static function formatDre(string $codigo) {
       
        $codigo = strval($codigo);
        
        $partes = str_split($codigo, 1);
        
        $i = 1;
        $b = 1;
        $parteFinal = "";
        foreach ($partes as $parte) {
            $parteFinal .= $i > 3 ? $parte : $parte.".";
            $i++;

            if($i > 3){
                if($b > 2){
                    $parteFinal .= ".";
                    $b = 1;
                }
                $b++;
            }
        }
        return rtrim($parteFinal,".");
    }

    public static function validarDre($string) {
        return preg_match('/^(?:\d)(?:\.\d){0,2}(?:\.\d{1,2})?$/', $string);
    }

    public static function sumTime($tempoInicial, $tempoAdicionar) {
        try {
            
            $datetime = new \DateTime($tempoInicial);
    
            list($horas, $minutos, $segundos) = explode(':', $tempoAdicionar);
    
            $interval = new \DateInterval("PT{$horas}H{$minutos}M{$segundos}S");
    
            $datetime->add($interval);
   
            return $datetime->format('H:i:s');
        } catch (\Exception $e) {
            Logger::error($e->getMessage().$e->getTraceAsString());
            return null;
        }
    }

    public static function subTime($tempoInicial, $tempoDiminuir) {
        try {
            $datetime = new \DateTime($tempoInicial);
    
            list($horas, $minutos, $segundos) = explode(':', $tempoDiminuir);

            $interval = new \DateInterval("PT{$horas}H{$minutos}M{$segundos}S");
    
            $datetime->sub($interval);

            return $datetime->format('H:i:s');
        } catch (\Exception $e) {
            Logger::error($e->getMessage().$e->getTraceAsString());
            return null;
        }
    }

    public static function sumDate($tempoInicial, $tempoAdicionar) {
        try {
            
            $datetime = new \DateTime($tempoInicial);
    
            list($horas, $minutos, $segundos) = explode(':', $tempoAdicionar);
    
            $interval = new \DateInterval("PT{$horas}H{$minutos}M{$segundos}S");
    
            $datetime->add($interval);
   
            return $datetime->format('Y-m-d H:i:s');
        } catch (\Exception $e) {
            Logger::error($e->getMessage().$e->getTraceAsString());
            return null;
        }
    }

    public static function subDate($tempoInicial, $tempoDiminuir) {
        try {
            $datetime = new \DateTime($tempoInicial);
    
            list($horas, $minutos, $segundos) = explode(':', $tempoDiminuir);

            $interval = new \DateInterval("PT{$horas}H{$minutos}M{$segundos}S");
    
            $datetime->sub($interval);

            return $datetime->format('Y-m-d H:i:s');
        } catch (\Exception $e) {
            Logger::error($e->getMessage().$e->getTraceAsString());
            return null;
        }
    }

    public static function getAbsolutePath($path) {
        $path = str_replace(array('/', '\\'), DIRECTORY_SEPARATOR, $path);
        $parts = array_filter(explode(DIRECTORY_SEPARATOR, $path), 'strlen');
        $absolutes = array();
        foreach ($parts as $part) {
            if ('.' == $part) continue;
            if ('..' == $part) {
                array_pop($absolutes);
            } else {
                $absolutes[] = $part;
            }
        }
        return implode(DIRECTORY_SEPARATOR, $absolutes);
    }

    public static function genereteId(){
        return \intval(self::onlynumber(\microtime()));
    }

    public static function getUserIP() {
        if (isset($_SERVER['HTTP_X_FORWARDED_FOR'])
            && isset($_SERVER['REMOTE_ADDR']))
        {
            $client_ips = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);

            return array_shift($client_ips);
        }
        elseif (isset($_SERVER['HTTP_CLIENT_IP'])
            && isset($_SERVER['REMOTE_ADDR']))
        {
            $client_ips = explode(',', $_SERVER['HTTP_CLIENT_IP']);

            return array_shift($client_ips);
        }
        elseif (isset($_SERVER['REMOTE_ADDR']))
        {
            return $_SERVER['REMOTE_ADDR'];
        }
    }

    public static function isMobile():bool
    {
        $useragent = $_SERVER['HTTP_USER_AGENT'];

        if(preg_match('/(android|bb\d+|meego).+mobile|avantgo|bada\/|blackberry|blazer|compal|elaine|fennec|hiptop|iemobile|ip(hone|od)|iris|kindle|lge |maemo|midp|mmp|netfront|opera m(ob|in)i|palm( os)?|phone|p(ixi|re)\/|plucker|pocket|psp|series(4|6)0|symbian|treo|up\.(browser|link)|vodafone|wap|windows (ce|phone)|xda|xiino/i',$useragent)||preg_match('/1207|6310|6590|3gso|4thp|50[1-6]i|770s|802s|a wa|abac|ac(er|oo|s\-)|ai(ko|rn)|al(av|ca|co)|amoi|an(ex|ny|yw)|aptu|ar(ch|go)|as(te|us)|attw|au(di|\-m|r |s )|avan|be(ck|ll|nq)|bi(lb|rd)|bl(ac|az)|br(e|v)w|bumb|bw\-(n|u)|c55\/|capi|ccwa|cdm\-|cell|chtm|cldc|cmd\-|co(mp|nd)|craw|da(it|ll|ng)|dbte|dc\-s|devi|dica|dmob|do(c|p)o|ds(12|\-d)|el(49|ai)|em(l2|ul)|er(ic|k0)|esl8|ez([4-7]0|os|wa|ze)|fetc|fly(\-|_)|g1 u|g560|gene|gf\-5|g\-mo|go(\.w|od)|gr(ad|un)|haie|hcit|hd\-(m|p|t)|hei\-|hi(pt|ta)|hp( i|ip)|hs\-c|ht(c(\-| |_|a|g|p|s|t)|tp)|hu(aw|tc)|i\-(20|go|ma)|i230|iac( |\-|\/)|ibro|idea|ig01|ikom|im1k|inno|ipaq|iris|ja(t|v)a|jbro|jemu|jigs|kddi|keji|kgt( |\/)|klon|kpt |kwc\-|kyo(c|k)|le(no|xi)|lg( g|\/(k|l|u)|50|54|\-[a-w])|libw|lynx|m1\-w|m3ga|m50\/|ma(te|ui|xo)|mc(01|21|ca)|m\-cr|me(rc|ri)|mi(o8|oa|ts)|mmef|mo(01|02|bi|de|do|t(\-| |o|v)|zz)|mt(50|p1|v )|mwbp|mywa|n10[0-2]|n20[2-3]|n30(0|2)|n50(0|2|5)|n7(0(0|1)|10)|ne((c|m)\-|on|tf|wf|wg|wt)|nok(6|i)|nzph|o2im|op(ti|wv)|oran|owg1|p800|pan(a|d|t)|pdxg|pg(13|\-([1-8]|c))|phil|pire|pl(ay|uc)|pn\-2|po(ck|rt|se)|prox|psio|pt\-g|qa\-a|qc(07|12|21|32|60|\-[2-7]|i\-)|qtek|r380|r600|raks|rim9|ro(ve|zo)|s55\/|sa(ge|ma|mm|ms|ny|va)|sc(01|h\-|oo|p\-)|sdk\/|se(c(\-|0|1)|47|mc|nd|ri)|sgh\-|shar|sie(\-|m)|sk\-0|sl(45|id)|sm(al|ar|b3|it|t5)|so(ft|ny)|sp(01|h\-|v\-|v )|sy(01|mb)|t2(18|50)|t6(00|10|18)|ta(gt|lk)|tcl\-|tdg\-|tel(i|m)|tim\-|t\-mo|to(pl|sh)|ts(70|m\-|m3|m5)|tx\-9|up(\.b|g1|si)|utst|v400|v750|veri|vi(rg|te)|vk(40|5[0-3]|\-v)|vm40|voda|vulc|vx(52|53|60|61|70|80|81|83|85|98)|w3c(\-| )|webc|whit|wi(g |nc|nw)|wmlb|wonu|x700|yas\-|your|zeto|zte\-/i',substr($useragent,0,4)))
            return true;

        return false;
    }

}
