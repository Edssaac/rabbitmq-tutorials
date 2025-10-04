> RabbitMQ: https://www.rabbitmq.com/getstarted.html

|           |               |                       |
|-----------|---------------|-----------------------|
| Producer  | Produtor      | Envia as mensagens    |
| Queue     | Fila          | Buffer de mensagens   |
| Consumer  | Consumidor    | Recebe as mensagens   |


Se alterar alguma coisa no código do Consumidor, então ele deve 
ser derrubado e subido novamente para as alterações refletirem.

Muitos produtores podem enviar mensagens para uma mesma fila. \
Uma mesma fila pode ser consumida por muitos consumidores.

RabbitMQ não permite redefinir uma fila existente com parâmetros 
diferentes e retornará um erro para qualquer programa que tenta fazer isso.


O protocolo AMQP pré-define um conjunto de 14 atributos que acompanham as mensagens. 
A maioria são raramente utilizadas, com excessão das seguintes:

|                       |                                                                                   |
|-----------------------|-----------------------------------------------------------------------------------|
| `delivery_mode`       | marca uma mensagem como persistente (com valor 2) ou transitória (com valor 1)    |
| `content_type`        | descreve o mime-type da codificação. Por exemplo, JSON: `application/json`        |
| `reply_to`            | normalmente utilizada para nomear uma fila de callback                            |
| `correlation_id`      | correlacionar respostas RPC com solicitações                                      |

<br>

Trabalhando com uma Fila com mais de um Consumidor:

`Round-robin`: Forma de distribuição em que por padrão, o RabbitMQ 
enviará cada mensagem para o próximo consumidor, em sequência. 
Em média, cada consumidor receberá o mesmo número de mensagens.

ack(nowledgement)   = re(conhecimento) \
unacknowledged      = não reconhecido

O 4° parâmetro do `basic_consume()` é o `"no_ack"`, por padrão o seu valor é `false` e 
significa que se uma mensagem entrar no consumidor ela já será retirada da fila.
Isso é um problema pois se o Consumidor morrer essa mensagem será perdida.
Devemos passar esse parâmetro como `true` e após o sucesso do processamento necessário, 
chamar o método `$message->ack()`.

O 3° parâmetro do `queue_declare()` é o `"durable"`, por padrão o seu valor é `false` e 
significa que se o RabbitMQ morrer, o seu conteúdo será perdido.
Devemos passar como `true` para que as mensagens não se percam mesmo nesse cenário.

As menagens precisam ser criadas como persistêntes, seguindo esse padrão:

```php
$message = new AMQPMessage(
    $data,
    [
        "delivery_mode" => AMQPMessage::DELIVERY_MODE_PERSISTENT
    ]
);
```

prefetch        = pré-busca \
fair dispatch   = despacho justo

O método `$channel->basic_qos()` determina com quantas mensagens um Consumidor
pode trabalhar por vez. Ou seja, uma nova mensagem não é despachada para o 
Consumidor até que ele termine o processamento da anterior, em vez disso 
essa mensagem é encaminhada para o próximo Consumidor que não esteja ocupado.

`Publish/Subscribe`: Padrão em que mensagens são enviadas para mais de um Consumidor. 
Essencialmente, as mensagens publicadas serão transmitidas para todos os receptores. 

`Exchange` (Troca): Recebe uma mensagem do Producer e decide o que fazer com ela. \
Ele pode enviar a mensagem para uma fila específica. \
Ele pode enviar a mensagem para várias filas. \
Ele pode descartar a mensagem. 

`Bindings` (Ligações): Relacionamento em que a `Exchange` envia a mensagem para a Fila.

As regras são definidas pelo tipo de Exchange. Os tipos são:

|                               |                                                       |
|-------------------------------|-------------------------------------------------------|
| `direct` (direto)             | filtrar as mensagens pelo `routing_key`*              |
| `topic` (tópico)              | filtrar as mensagens por padrões no `binding_key`*    |
| `headers` (cabeçalhos)        | -                                                     |
| `fanout` (fan out = espalham) | pegar todas as mensagens disponíveis                  |

*`routing_key`: deve ser uma lista de palavras separadas por pontos:
`"<speed>.<colour>.<species>"` \
*`binding_key`: segue a mesma lógica, mas com algumas adições: \
`*` (estrela) pode substituir exatamente uma palavra:                 `*.*.rabbit` \
`#` (hash) pode substituir zero ou mais palavras:                     `lazy.#`

O Exchange do tipo Topic é versátil pois pode simular o Direct e o Fanout:

Quando uma Fila está vinculada com "#" no binding_key, ela vai receber 
todas as mensagens, assim como no Fanout.

Quando os caracteres especiais "*" e "#" não são usados na vinculação, 
então o Topic se comportará como no Direct.

RPC = Remote Procedure Call
Protocolo em que um programa faz uma requisição 
para um serviço localizado em outro computador.