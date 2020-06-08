# Rest Api UCL

Api desenvolvida para extrair as informações do portal do aluno da [Faculdade UCL](https://www.ucl.br/)

## Requisitos

* PHP 5.6
* cURL

## Requests
#### Em todos os requests deve ser passado o usuário e a senha do aluno via POST via form ("application/x-www-form-urlencoded")
Exemplo de request em __C#__ utilizando a lib __RestSharp__
```C#
var client = new RestClient("localhost/ApiUcl/v1/student/profile");
var request = new RestRequest(Method.POST);
request.AddHeader("Content-Type", "application/x-www-form-urlencoded");
request.AddParameter("user", "USUÁRIO");
request.AddParameter("password", "SENHA");
IRestResponse response = client.Execute(request);
Console.WriteLine(response.Content);
```

Requests disponíveis:
* __/student/profile__  perfil do aluno
* __/student/grades__ todas as notas do aluno
* __/student/financial__ todas as mensalidades do aluno

### Possíveis erros
| Código do erro  |  Mensagem  |
| ------------------- | ------------------- |
|  1 | Parametros incorretos ao efetuar o request |
|  2 | Parametros incorretos ao efetuar o login |
|  3 | Usuário e/ou senha inválidos |
|  4 | Response inválido |

## License
[MIT](https://choosealicense.com/licenses/mit/)