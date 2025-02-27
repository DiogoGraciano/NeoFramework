<?php

namespace Core;

final class Response
{
    private int $code = 200;
    private array $headers = [];
    private bool $isSent = false;
    private array $content = [];

    public function setCode(int $code):self
    {
        $this->code = $code;
        http_response_code($code);

        return $this;
    }

    public function getCode(): int
    {
        return $this->code;
    }

    public function isSent(): bool
    {
        return $this->isSent;
    }

    public function setHeader(string $name, string $value):self
    {
        $this->headers[$name] = [$value];

        return $this;
    }

    public function addHeader(string $name, string $value):self
    {
        $this->headers[$name][] = $value;

        return $this;
    }

    public function getHeader(string $name)
    {
        return $this->headers[$name] ?? null;
    }

    public function getHeaders(): array
    {
        return $this->headers;
    }

    public function setContentType(string $type, ?string $charset = null):self
    {
        $value = $type;
        if ($charset) {
            $value .= "; charset=$charset";
        }
        $this->setHeader('Content-Type', $value);

        return $this;
    }

    public function setExpiration(?string $time):self
    {
        if ($time === null) {
            $this->setHeader('Expires', '0');
        } else {
            $this->setHeader('Expires', gmdate('D, d M Y H:i:s', strtotime($time)) . ' GMT');
        }

        return $this;
    }

    public function setCookie(
        string $name,
        string $value,
        string|int|\DateTimeInterface|null $expire,
        ?string $path = null,
        ?string $domain = null,
        ?bool $secure = null,
        ?bool $httpOnly = null,
        ?string $sameSite = null
    ):self 
    {
        if ($expire instanceof \DateTimeInterface) {
            $expire = $expire->getTimestamp();
        } elseif (is_string($expire)) {
            $expire = strtotime($expire);
        }
        setcookie($name, $value, [
            'expires' => $expire,
            'path' => $path,
            'domain' => $domain,
            'secure' => $secure,
            'httponly' => $httpOnly,
            'samesite' => $sameSite,
        ]);

        return $this;
    }

    public function deleteCookie(string $name, ?string $path = null, ?string $domain = null, ?bool $secure = null):self
    {
        return $this->setCookie($name, '', time() - 3600, $path, $domain, $secure, true);
    }

    public function go(string $caminho): self
    {
        $this->setHeader('Location', Url::getUrlBase() . $caminho);
        return $this;
    }

    public function goToSite(string $caminho): self
    {
        $this->setHeader('Location',$caminho);
        return $this;
    }

    public function addContent(object|string|array $content):self
    {
        if(is_object($content) && is_subclass_of($content,"Core\Abstract\Layout")){
            $this->content[] = $content->parse();
            return $this;
        }

        if(is_array($content) || is_object($content)){
            $this->content[] = json_encode($content);
            return $this;
        }

        $this->content[] = $content;

        return $this;
    }

    public function getContents():array
    {
        return $this->content;
    }

    public function send()
    {
        if ($this->isSent) {
            throw new \Exception("A resposta já foi enviada.");
        }

        foreach ($this->headers as $name => $values) {
            foreach ($values as $value) {
                header("$name: $value", false);
            }
        }

        foreach($this->content as $content)
        {
            echo $content;
        }

        $this->isSent = true;

        exit;
    }
}
