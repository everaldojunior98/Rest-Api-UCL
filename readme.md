# Rest Api UCL

Api desenvolvida para extrair as informações do portal do aluno da [Faculdade UCL](https://www.ucl.br/).

## Requisitos

* PHP 7
* cURL

## Como usar:
Basta fazer um request do tipo POST para o __URL__ desejada, informando o __USUÁRIO__ e a __SENHA__ no body sendo o body do tipo "application/x-www-form-urlencoded".

Exemplo de request em __C#__ utilizando a lib __RestSharp__
```C#
var client = new RestClient("URL");
var request = new RestRequest(Method.POST);
request.AddHeader("Content-Type", "application/x-www-form-urlencoded");
request.AddParameter("user", "USUÁRIO");
request.AddParameter("password", "SENHA");
IRestResponse response = client.Execute(request);
Console.WriteLine(response.Content);
```

Exemplo de request em __Python__ utilizando a lib __Requests__
```Python
import requests

url = "URL"

payload='user=USUÁRIO&password=SENHA'
headers = {
  'Content-Type': 'application/x-www-form-urlencoded'
}

response = requests.request("POST", url, headers=headers, data=payload)

print(response.text)
```

Exemplo de request em __Java__ utilizando a lib __OkHttp__
```Java
import java.io.*;
import okhttp3.*;
public class main {
  public static void main(String []args) throws IOException{
    OkHttpClient client = new OkHttpClient().newBuilder()
      .build();
    MediaType mediaType = MediaType.parse("application/x-www-form-urlencoded");
    RequestBody body = RequestBody.create(mediaType, "user=USUÁRIO&password=SENHA");
    Request request = new Request.Builder()
      .url("URL")
      .method("POST", body)
      .addHeader("Content-Type", "application/x-www-form-urlencoded")
      .build();
    Response response = client.newCall(request).execute();
    System.out.println(response.body().string());
  }
}
```

## URL's disponíveis:
1. __/v2/WebAluno/Login__
2. __/v2/WebAluno/QuadroDeNotas__
3. __/v2/WebAluno/Financeiro__
4. __/v2/WebAluno/HorarioIndividual__
5. __/v2/WebAluno/PautasCursadas__
6. __/v2/WebAluno/Avisos__
7. __/v2/WebAluno/Estagio__

### Erros
Sempre que acontecer um erro o request irá retornar o status __code 500__ e a mensagem do erro estará dentro do __Header__ no campo __X-Error-Message__.

## License
[MIT](https://choosealicense.com/licenses/mit/)
