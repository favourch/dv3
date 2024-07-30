<?php

return [
    [
      'title' => 'contacts',
      'value' => [
        [
          'title' => 'Get contact list',
          'method' => 'get',
          'route' => '/api/contacts',
          'request' => [
              'curl' => "curl -X GET \n {{base_url}}/api/contacts \n &nbsp; -H 'Authorization: Bearer your-bearer-token' \n &nbsp; -H 'Content-Type: application/json'",
              'php' => "<?php\n\$client = new Client();\n\$request = new Request('GET', '{{base_url}}/api/contacts');\n\$res = \$client->sendAsync(\$request)->wait();\n\$responseBody = \$res->getBody();\n\n// Use \$responseBody as needed\n",
              'nodejs' => "var axios = require('axios');\n\nvar config = {\n  method: 'get',\n  maxBodyLength: Infinity,\n  url: '{{base_url}}/api/contacts',\n  headers: { }\n};\n\naxios(config)\n.then(function (response) {\n  console.log(JSON.stringify(response.data));\n})\n.catch(function (error) {\n  console.log(error);\n});",
              'python' => "import requests\n\nurl = \"{{base_url}}/api/contacts\"\n\npayload={}\nheaders = {}\n\nresponse = requests.request(\"GET\", url, headers=headers, data=payload)\n\nprint(response.text)",
              'java' => "OkHttpClient client = new OkHttpClient().newBuilder()\n  .build();\nMediaType mediaType = MediaType.parse(\"text/plain\");\nRequestBody body = RequestBody.create(mediaType, \"\");\nRequest request = new Request.Builder()\n  .url(\"{{base_url}}/api/contacts\")\n  .method(\"GET\", body)\n  .build();\nResponse response = client.newCall(request).execute();",
              'ruby' => "require \"uri\"\nrequire \"net/http\"\n\nurl = URI(\"{{base_url}}/api/contacts\")\n\nhttp = Net::HTTP.new(url.host, url.port);\nrequest = Net::HTTP::Get.new(url)\n\nresponse = http.request(request)\nputs response.read_body",
          ]
        ],
        [
          'title' => 'Add contact',
          'method' => 'post',
          'route' => '/api/contacts',
          'request' => [
              'curl' => "curl -X POST \n {{base_url}}/api/contacts \n &nbsp; -H 'Authorization: Bearer your-bearer-token' \n &nbsp; -H 'Content-Type: application/json'",
              'php' => "<?php\n\$client = new Client();\n\$body = '{\n    \"first_name\": \"John\",\n    \"last_name\": \"Doe\",\n    \"email\": \"johndoe@gmail.com\",\n    \"phone\": \"+1 (968) 082-5846\"\n}';\n\$request = new Request('POST', '{{base_url}}/api/contacts', [], \$body);\n\$res = \$client->sendAsync(\$request)->wait();\n\$responseBody = \$res->getBody();\necho \$responseBody;\n?>",
              'nodejs' => "var axios = require('axios');\nvar data = '{\n    \"first_name\": \"John\",\n    \"last_name\": \"Doe\",\n    \"email\": \"johndoe@gmail.com\",\n    \"phone\": \"+1 (968) 082-5846\"\n}';\n\nvar config = {\n  method: 'post',\n  maxBodyLength: Infinity,\n  url: '{{base_url}}/api/contacts',\n  headers: { },\n  data: data\n};\n\naxios(config)\n  .then(function (response) {\n    console.log(JSON.stringify(response.data));\n  })\n  .catch(function (error) {\n    console.log(error);\n  });",
              'python' => "import requests\n\nurl = \"{{base_url}}/api/contacts\"\n\npayload = \"{\\n    \\\"first_name\\\": \\\"John\\\",\\n    \\\"last_name\\\": \\\"Doe\\\",\\n    \\\"email\\\": \\\"johndoe@gmail.com\\\",\\n    \\\"phone\\\": \\\"+1 (968) 082-5846\\\"\\n}\"\nheaders = {}\n\nresponse = requests.request(\"POST\", url, headers=headers, data=payload)\n\nprint(response.text)",
              'java' => "OkHttpClient client = new OkHttpClient().newBuilder()\n  .build();\nMediaType mediaType = MediaType.parse(\"text/plain\");\nRequestBody body = RequestBody.create(mediaType, \"{\\n    \\\"first_name\\\": \\\"John\\\",\\n    \\\"last_name\\\": \\\"Doe\\\",\\n    \\\"email\\\": \\\"johndoe@gmail.com\\\",\\n    \\\"phone\\\": \\\"+1 (968) 082-5846\\\"\\n}\");\nRequest request = new Request.Builder()\n  .url(\"{{base_url}}/api/contacts\")\n  .method(\"POST\", body)\n  .build();\nResponse response = client.newCall(request).execute();",
              'ruby' => "require \"uri\"\nrequire \"net/http\"\n\nurl = URI(\"{{base_url}}/api/contacts\")\n\nhttp = Net::HTTP.new(url.host, url.port);\nrequest = Net::HTTP::Post.new(url)\nrequest.body = \"{\\n    \\\"first_name\\\": \\\"John\\\",\\n    \\\"last_name\\\": \\\"Doe\\\",\\n    \\\"email\\\": \\\"johndoe@gmail.com\\\",\\n    \\\"phone\\\": \\\"+1 (968) 082-5846\\\"\\n}\"\n\nresponse = http.request(request)\nputs response.read_body",
          ]
        ],
        [
          'title' => 'Edit contact',
          'method' => 'put',
          'route' => '/api/contacts/{uuid}',
          'request' => [
              'curl' => "curl -X PUT \n {{base_url}}/api/contacts/{uuid} \n &nbsp; -H 'Authorization: Bearer your-bearer-token' \n &nbsp; -H 'Content-Type: application/json'",
              'php' => "<?php\n\$client = new Client();\n\$headers = [\n  'Content-Type' => 'application/json'\n];\n\$body = '{\n  \"first_name\": \"John\",\n  \"last_name\": \"Doe\",\n  \"email\": \"johndoe@gmail.com\",\n  \"phone\": \"+1 (968) 082-5846\"\n}';\n\$request = new Request('PUT', '{{base_url}}/api/contacts/{{uuid}}', \$headers, \$body);\n\$res = \$client->sendAsync(\$request)->wait();\n\$responseBody = \$res->getBody();\necho \$responseBody;\n?>",
              'nodejs' => "var axios = require('axios');\nvar data = JSON.stringify({\n  \"first_name\": \"John\",\n  \"last_name\": \"Doe\",\n  \"email\": \"johndoe@gmail.com\",\n  \"phone\": \"+1 (968) 082-5846\"\n});\n\nvar config = {\n  method: 'put',\n  maxBodyLength: Infinity,\n  url: '{{base_url}}/api/contacts/{{uuid}}',\n  headers: { \n    'Content-Type': 'application/json'\n  },\n  data : data\n};\n\naxios(config)\n.then(function (response) {\n  console.log(JSON.stringify(response.data));\n})\n.catch(function (error) {\n  console.log(error);\n});",
              'python' => "import requests\nimport json\n\nurl = \"{{base_url}}/api/contacts/{{uuid}}\"\n\npayload = json.dumps({\n  \"first_name\": \"John\",\n  \"last_name\": \"Doe\",\n  \"email\": \"johndoe@gmail.com\",\n  \"phone\": \"+1 (968) 082-5846\"\n})\nheaders = {\n  'Content-Type': 'application/json'\n}\n\nresponse = requests.request(\"PUT\", url, headers=headers, data=payload)\n\nprint(response.text)",
              'java' => "OkHttpClient client = new OkHttpClient().newBuilder()\n  .build();\nMediaType mediaType = MediaType.parse(\"application/json\");\nRequestBody body = RequestBody.create(mediaType, \"{\\n    \\\"first_name\\\": \\\"John\\\",\\n    \\\"last_name\\\": \\\"Doe\\\",\\n    \\\"email\\\": \\\"johndoe@gmail.com\\\",\\n    \\\"phone\\\": \\\"+1 (968) 082-5846\\\"\\n}\");\nRequest request = new Request.Builder()\n  .url(\"{{base_url}}/api/contacts/{{uuid}}\")\n  .method(\"PUT\", body)\n  .addHeader(\"Content-Type\", \"application/json\")\n  .build();\nResponse response = client.newCall(request).execute();",
              'ruby' => "require \"uri\"\nrequire \"json\"\nrequire \"net/http\"\n\nurl = URI(\"{{base_url}}/api/contacts/945d8b4f-a580-4743-bdfa-f0095059b37a\")\n\nhttp = Net::HTTP.new(url.host, url.port);\nrequest = Net::HTTP::Put.new(url)\nrequest[\"Content-Type\"] = \"application/json\"\nrequest.body = JSON.dump({\n  \"first_name\": \"John\",\n  \"last_name\": \"Doe\",\n  \"email\": \"johndoe@gmail.com\",\n  \"phone\": \"+1 (968) 082-5846\"\n})\n\nresponse = http.request(request)\nputs response.read_body",
          ]
        ],
        [
          'title' => 'Delete contact',
          'method' => 'del',
          'route' => '/api/contacts/{uuid}',
          'request' => [
              'curl' => "curl -X DELETE \n {{base_url}}/api/contacts/{uuid} \n &nbsp; -H 'Authorization: Bearer your-bearer-token' \n &nbsp; -H 'Content-Type: application/json'",
              'php' => "<?php\n\$client = new Client();\n\$request = new Request('DELETE', '{{base_url}}/api/contacts/{{uuid}}');\n\$res = \$client->sendAsync(\$request)->wait();\n\$responseBody = \$res->getBody();\necho \$responseBody;\n?>",
              'nodejs' => "var axios = require('axios');\n\nvar config = {\n  method: 'delete',\n  maxBodyLength: Infinity,\n  url: '{{base_url}}/api/contacts/{{uuid}}',\n  headers: { }\n};\n\naxios(config)\n  .then(function (response) {\n    console.log(JSON.stringify(response.data));\n  })\n  .catch(function (error) {\n    console.log(error);\n  });",
              'python' => "import requests\n\nurl = \"{{base_url}}/api/contacts/{{uuid}}\"\n\npayload={}\nheaders = {}\n\nresponse = requests.request(\"DELETE\", url, headers=headers, data=payload)\n\nprint(response.text)",
              'java' => "OkHttpClient client = new OkHttpClient().newBuilder()\n  .build();\nMediaType mediaType = MediaType.parse(\"text/plain\");\nRequestBody body = RequestBody.create(mediaType, \"\");\nRequest request = new Request.Builder()\n  .url(\"{{base_url}}/api/contacts/{{uuid}}\")\n  .method(\"DELETE\", body)\n  .build();\nResponse response = client.newCall(request).execute();",
              'ruby' => "require \"uri\"\nrequire \"net/http\"\n\nurl = URI(\"{{base_url}}/api/contacts/945d8b4f-a580-4743-bdfa-f0095059b37a\")\n\nhttp = Net::HTTP.new(url.host, url.port);\nrequest = Net::HTTP::Delete.new(url)\n\nresponse = http.request(request)\nputs response.read_body",
          ]
        ],
      ],
    ],
    [
      'title' => 'Contact groups',
      'value' => [
        [
          'title' => 'Get contact group list',
          'method' => 'get',
          'route' => '/api/contact-groups',
          'request' => [
              'curl' => "curl -X GET \n {{base_url}}/api/contact-groups \n &nbsp; -H 'Authorization: Bearer your-bearer-token' \n &nbsp; -H 'Content-Type: application/json'",
              'php' => "<?php\n\$client = new Client();\n\$request = new Request('GET', '{{base_url}}/api/contact-groups');\n\$res = \$client->sendAsync(\$request)->wait();\n\$responseBody = \$res->getBody();\necho \$responseBody;\n?>",
              'nodejs' => "var axios = require('axios');\n\nvar config = {\n  method: 'get',\n  maxBodyLength: Infinity,\n  url: '{{base_url}}/api/contact-groups',\n  headers: { }\n};\n\naxios(config)\n  .then(function (response) {\n    console.log(JSON.stringify(response.data));\n  })\n  .catch(function (error) {\n    console.log(error);\n  });",
              'python' => "import requests\n\nurl = \"{{base_url}}/api/contact-groups\"\n\npayload={}\nheaders = {}\n\nresponse = requests.request(\"GET\", url, headers=headers, data=payload)\n\nprint(response.text)",
              'java' => "OkHttpClient client = new OkHttpClient().newBuilder()\n  .build();\nMediaType mediaType = MediaType.parse(\"text/plain\");\nRequestBody body = RequestBody.create(mediaType, \"\");\nRequest request = new Request.Builder()\n  .url(\"{{base_url}}/api/contact-groups\")\n  .method(\"GET\", body)\n  .build();\nResponse response = client.newCall(request).execute();",
              'ruby' => "require \"uri\"\nrequire \"net/http\"\n\nurl = URI(\"{{base_url}}/api/contact-groups\")\n\nhttp = Net::HTTP.new(url.host, url.port);\nrequest = Net::HTTP::Get.new(url)\n\nresponse = http.request(request)\nputs response.read_body",
          ]
        ],
        [
          'title' => 'Add contact group',
          'method' => 'post',
          'route' => '/api/contact-groups',
          'request' => [
              'curl' => "curl -X POST \n {{base_url}}/api/contact-groups \n &nbsp; -H 'Authorization: Bearer your-bearer-token' \n &nbsp; -H 'Content-Type: application/json'",
              'php' => "<?php\n\$client = new Client();\n\$body = '{\n    \"name\":\"Lead 4\"\n}';\n\$request = new Request('POST', '{{base_url}}/api/contact-groups', [], \$body);\n\$res = \$client->sendAsync(\$request)->wait();\n\$responseBody = \$res->getBody();\necho \$responseBody;\n?>",
              'nodejs' => "var axios = require('axios');\nvar data = '{\n    \"name\":\"Lead 5\"\n}';\n\nvar config = {\n  method: 'post',\n  maxBodyLength: Infinity,\n  url: '{{base_url}}/api/contact-groups',\n  headers: { },\n  data: data\n};\n\naxios(config)\n  .then(function (response) {\n    console.log(JSON.stringify(response.data));\n  })\n  .catch(function (error) {\n    console.log(error);\n  });",
              'python' => "import requests\n\nurl = \"{{base_url}}/api/contact-groups\"\n\npayload = \"{\\n    \\\"name\\\":\\\"Lead 5\\\"\\n}\"\nheaders = {}\n\nresponse = requests.request(\"POST\", url, headers=headers, data=payload)\n\nprint(response.text)",
              'java' => "OkHttpClient client = new OkHttpClient().newBuilder()\n  .build();\nMediaType mediaType = MediaType.parse(\"text/plain\");\nRequestBody body = RequestBody.create(mediaType, \"{\\n    \\\"name\\\":\\\"Lead 5\\\"\\n}\");\nRequest request = new Request.Builder()\n  .url(\"{{base_url}}/api/contact-groups\")\n  .method(\"POST\", body)\n  .build();\nResponse response = client.newCall(request).execute();",
              'ruby' => "require \"uri\"\nrequire \"net/http\"\n\nurl = URI(\"{{base_url}}/api/contact-groups\")\n\nhttp = Net::HTTP.new(url.host, url.port);\nrequest = Net::HTTP::Post.new(url)\nrequest.body = \"{\\n    \\\"name\\\":\\\"Lead 5\\\"\\n}\"\n\nresponse = http.request(request)\nputs response.read_body",
          ]
        ],
        [
          'title' => 'Edit contact group',
          'method' => 'put',
          'route' => '/api/contact-groups/{uuid}',
          'request' => [
              'curl' => "curl -X PUT \n {{base_url}}/api/contact-groups/{uuid} \n &nbsp; -H 'Authorization: Bearer your-bearer-token' \n &nbsp; -H 'Content-Type: application/json'",
              'php' => "<?php\n\$client = new Client();\n\$body = '{\n    \"name\":\"Lead 5\"\n}';\n\$request = new Request('PUT', '{{base_url}}/api/contact-groups/{{uuid}}', [], \$body);\n\$res = \$client->sendAsync(\$request)->wait();\n\$responseBody = \$res->getBody();\necho \$responseBody;\n?>",
              'nodejs' => "var axios = require('axios');\nvar data = '{\n    \"name\":\"Lead 5\"\n}';\n\nvar config = {\n  method: 'put',\n  maxBodyLength: Infinity,\n  url: '{{base_url}}/api/contact-groups/{{id}}',\n  headers: { },\n  data: data\n};\n\naxios(config)\n  .then(function (response) {\n    console.log(JSON.stringify(response.data));\n  })\n  .catch(function (error) {\n    console.log(error);\n  });",
              'python' => "import requests\n\nurl = \"{{base_url}}/api/contact-groups/{{id}}\"\n\npayload = \"{\\n    \\\"name\\\":\\\"Lead 5\\\"\\n}\"\nheaders = {}\n\nresponse = requests.request(\"PUT\", url, headers=headers, data=payload)\n\nprint(response.text)",
              'java' => "OkHttpClient client = new OkHttpClient().newBuilder()\n  .build();\nMediaType mediaType = MediaType.parse(\"text/plain\");\nRequestBody body = RequestBody.create(mediaType, \"{\\n    \\\"name\\\":\\\"Lead 5\\\"\\n}\");\nRequest request = new Request.Builder()\n  .url(\"{{base_url}}/api/contact-groups/{{id}}\")\n  .method(\"PUT\", body)\n  .build();\nResponse response = client.newCall(request).execute();",
              'ruby' => "require \"uri\"\nrequire \"net/http\"\n\nurl = URI(\"{{base_url}}/api/contact-groups/{{id}}\")\n\nhttp = Net::HTTP.new(url.host, url.port);\nrequest = Net::HTTP::Put.new(url)\nrequest.body = \"{\\n    \\\"name\\\":\\\"Lead 5\\\"\\n}\"\n\nresponse = http.request(request)\nputs response.read_body",
          ]
        ],
        [
          'title' => 'Delete contact group',
          'method' => 'del',
          'route' => '/api/contact-groups/{uuid}',
          'request' => [
              'curl' => "curl -X DELETE \n {{base_url}}/api/contact-groups/{uuid} \n &nbsp; -H 'Authorization: Bearer your-bearer-token' \n &nbsp; -H 'Content-Type: application/json'",
              'php' => "<?php\n\$client = new Client();\n\$request = new Request('DELETE', '{{base_url}}/api/contact-groups/{{uuid}}');\n\$res = \$client->sendAsync(\$request)->wait();\n\$responseBody = \$res->getBody();\necho \$responseBody;\n?>",
              'nodejs' => "var axios = require('axios');\n\nvar config = {\n  method: 'delete',\n  maxBodyLength: Infinity,\n  url: '{{base_url}}/api/contact-groups/{{id}}',\n  headers: { }\n};\n\naxios(config)\n  .then(function (response) {\n    console.log(JSON.stringify(response.data));\n  })\n  .catch(function (error) {\n    console.log(error);\n  });",
              'python' => "import requests\n\nurl = \"{{base_url}}/api/contact-groups/{{id}}\"\n\npayload={}\nheaders = {}\n\nresponse = requests.request(\"DELETE\", url, headers=headers, data=payload)\n\nprint(response.text)",
              'java' => "OkHttpClient client = new OkHttpClient().newBuilder()\n  .build();\nMediaType mediaType = MediaType.parse(\"text/plain\");\nRequestBody body = RequestBody.create(mediaType, \"\");\nRequest request = new Request.Builder()\n  .url(\"{{base_url}}/api/contact-groups/{{id}}\")\n  .method(\"DELETE\", body)\n  .build();\nResponse response = client.newCall(request).execute();",
              'ruby' => "require \"uri\"\nrequire \"net/http\"\n\nurl = URI(\"{{base_url}}/api/contact-groups/{{id}}\")\n\nhttp = Net::HTTP.new(url.host, url.port);\nrequest = Net::HTTP::Delete.new(url)\n\nresponse = http.request(request)\nputs response.read_body",
          ]
        ],
      ],
    ],
    [
      'title' => 'Canned replies',
      'value' => [
        [
          'title' => 'Get canned replies list',
          'method' => 'get',
          'route' => '/api/canned-replies',
          'request' => [
              'curl' => "curl -X GET \n {{base_url}}/api/canned-replies \n &nbsp; -H 'Authorization: Bearer your-bearer-token' \n &nbsp; -H 'Content-Type: application/json'",
              'php' => "<?php\n\$client = new Client();\n\$request = new Request('GET', '{{base_url}}/api/canned-replies');\n\$res = \$client->sendAsync(\$request)->wait();\n\$responseBody = \$res->getBody();\necho \$responseBody;\n?>",
              'nodejs' => "var axios = require('axios');\n\nvar config = {\n  method: 'get',\n  maxBodyLength: Infinity,\n  url: '{{base_url}}/api/canned-replies',\n  headers: { }\n};\n\naxios(config)\n  .then(function (response) {\n    console.log(JSON.stringify(response.data));\n  })\n  .catch(function (error) {\n    console.log(error);\n  });",
              'python' => "import requests\n\nurl = \"{{base_url}}/api/canned-replies\"\n\npayload={}\nheaders = {}\n\nresponse = requests.request(\"GET\", url, headers=headers, data=payload)\n\nprint(response.text)",
              'java' => "OkHttpClient client = new OkHttpClient().newBuilder()\n  .build();\nMediaType mediaType = MediaType.parse(\"text/plain\");\nRequestBody body = RequestBody.create(mediaType, \"\");\nRequest request = new Request.Builder()\n  .url(\"{{base_url}}/api/canned-replies\")\n  .method(\"GET\", body)\n  .build();\nResponse response = client.newCall(request).execute();",
              'ruby' => "require \"uri\"\nrequire \"net/http\"\n\nurl = URI(\"{{base_url}}/api/canned-replies\")\n\nhttp = Net::HTTP.new(url.host, url.port);\nrequest = Net::HTTP::Get.new(url)\n\nresponse = http.request(request)\nputs response.read_body",
          ]
        ],
        [
          'title' => 'Add canned reply',
          'method' => 'post',
          'route' => '/api/canned-replies',
          'request' => [
              'curl' => "curl -X POST \n {{base_url}}/api/canned-replies \n &nbsp; -H 'Authorization: Bearer your-bearer-token' \n &nbsp; -H 'Content-Type: application/json'",
              'php' => "<?php\n\$client = new Client();\n\$body = '{\n    \"name\":\"About Us\",\n    \"trigger\": \"what do you do?\",\n    \"match_criteria\": \"contains\",\n    \"response_type\": \"text\",\n    \"response\": \"We sell shoes\"\n}';\n\$request = new Request('POST', '{{base_url}}/api/canned-replies', [], \$body);\n\$res = \$client->sendAsync(\$request)->wait();\n\$responseBody = \$res->getBody();\necho \$responseBody;\n?>",
              'nodejs' => "var axios = require('axios');\nvar data = '{\n    \"name\":\"About Us\",\n    \"trigger\": \"what do you do?\",\n    \"match_criteria\": \"contains\",\n    \"response_type\": \"text\",\n    \"response\": \"We sell shoes\"\n}';\n\nvar config = {\n  method: 'post',\n  maxBodyLength: Infinity,\n  url: '{{base_url}}/api/canned-replies',\n  headers: { },\n  data: data\n};\n\naxios(config)\n  .then(function (response) {\n    console.log(JSON.stringify(response.data));\n  })\n  .catch(function (error) {\n    console.log(error);\n  });",
              'python' => "import requests\n\nurl = \"{{base_url}}/api/canned-replies\"\n\npayload = \"{\\n    \\\"name\\\":\\\"About Us\\\",\\n    \\\"trigger\\\": \\\"what do you do?\\\",\\n    \\\"match_criteria\\\": \\\"contains\\\",\\n    \\\"response_type\\\": \\\"text\\\",\\n    \\\"response\\\": \\\"We sell shoes\\\"\\n}\"\nheaders = {}\n\nresponse = requests.request(\"POST\", url, headers=headers, data=payload)\n\nprint(response.text)",
              'java' => "OkHttpClient client = new OkHttpClient().newBuilder()\n  .build();\nMediaType mediaType = MediaType.parse(\"text/plain\");\nRequestBody body = RequestBody.create(mediaType, \"{\\n    \\\"name\\\":\\\"About Us\\\",\\n    \\\"trigger\\\": \\\"what do you do?\\\",\\n    \\\"match_criteria\\\": \\\"contains\\\",\\n    \\\"response_type\\\": \\\"text\\\",\\n    \\\"response\\\": \\\"We sell shoes\\\"\\n}\");\nRequest request = new Request.Builder()\n  .url(\"{{base_url}}/api/canned-replies\")\n  .method(\"POST\", body)\n  .build();\nResponse response = client.newCall(request).execute();",
              'ruby' => "require \"uri\"\nrequire \"net/http\"\n\nurl = URI(\"{{base_url}}/api/canned-replies\")\n\nhttp = Net::HTTP.new(url.host, url.port);\nrequest = Net::HTTP::Post.new(url)\nrequest.body = \"{\\n    \\\"name\\\":\\\"About Us\\\",\\n    \\\"trigger\\\": \\\"what do you do?\\\",\\n    \\\"match_criteria\\\": \\\"contains\\\",\\n    \\\"response_type\\\": \\\"text\\\",\\n    \\\"response\\\": \\\"We sell shoes\\\"\\n}\"\n\nresponse = http.request(request)\nputs response.read_body",
          ]
        ],
        [
          'title' => 'Edit canned reply',
          'method' => 'put',
          'route' => '/api/canned-replies/{uuid}',
          'request' => [
              'curl' => "curl -X PUT \n {{base_url}}/api/canned-replies/{uuid} \n &nbsp; -H 'Authorization: Bearer your-bearer-token' \n &nbsp; -H 'Content-Type: application/json'",
              'php' => "<?php\n\$client = new Client();\n\$body = '{\n    \"name\":\"About Us\",\n    \"trigger\": \"what do you do?\",\n    \"match_criteria\": \"contains\",\n    \"response_type\": \"text\",\n    \"response\": \"We sell shoes and clothes\"\n}';\n\$request = new Request('PUT', '{{base_url}}/api/canned-replies/{{uuid}}', [], \$body);\n\$res = \$client->sendAsync(\$request)->wait();\n\$responseBody = \$res->getBody();\necho \$responseBody;\n?>",
              'nodejs' => "var axios = require('axios');\nvar data = '{\n    \"name\":\"About Us\",\n    \"trigger\": \"what do you do?\",\n    \"match_criteria\": \"contains\",\n    \"response_type\": \"text\",\n    \"response\": \"We sell shoes and clothes\"\n}';\n\nvar config = {\n  method: 'put',\n  maxBodyLength: Infinity,\n  url: '{{base_url}}/api/canned-replies/{{uuid}}',\n  headers: { },\n  data: data\n};\n\naxios(config)\n  .then(function (response) {\n    console.log(JSON.stringify(response.data));\n  })\n  .catch(function (error) {\n    console.log(error);\n  });",
              'python' => "import requests\n\nurl = \"{{base_url}}/api/canned-replies/{{id}}\"\n\npayload = \"{\\n    \\\"name\\\":\\\"About Us\\\",\\n    \\\"trigger\\\": \\\"what do you do?\\\",\\n    \\\"match_criteria\\\": \\\"contains\\\",\\n    \\\"response_type\\\": \\\"text\\\",\\n    \\\"response\\\": \\\"We sell shoes and clothes\\\"\\n}\"\nheaders = {}\n\nresponse = requests.request(\"PUT\", url, headers=headers, data=payload)\n\nprint(response.text)",
              'java' => "OkHttpClient client = new OkHttpClient().newBuilder()\n  .build();\nMediaType mediaType = MediaType.parse(\"text/plain\");\nRequestBody body = RequestBody.create(mediaType, \"{\\n    \\\"name\\\":\\\"About Us\\\",\\n    \\\"trigger\\\": \\\"what do you do?\\\",\\n    \\\"match_criteria\\\": \\\"contains\\\",\\n    \\\"response_type\\\": \\\"text\\\",\\n    \\\"response\\\": \\\"We sell shoes and clothes\\\"\\n}\");\nRequest request = new Request.Builder()\n  .url(\"{{base_url}}/api/canned-replies/{{id}}\")\n  .method(\"PUT\", body)\n  .build();\nResponse response = client.newCall(request).execute();",
              'ruby' => "require \"uri\"\nrequire \"net/http\"\n\nurl = URI(\"{{base_url}}/api/canned-replies/{{id}}\")\n\nhttp = Net::HTTP.new(url.host, url.port);\nrequest = Net::HTTP::Put.new(url)\nrequest.body = \"{\\n    \\\"name\\\":\\\"About Us\\\",\\n    \\\"trigger\\\": \\\"what do you do?\\\",\\n    \\\"match_criteria\\\": \\\"contains\\\",\\n    \\\"response_type\\\": \\\"text\\\",\\n    \\\"response\\\": \\\"We sell shoes and clothes\\\"\\n}\"\n\nresponse = http.request(request)\nputs response.read_body",
          ]
        ],
        [
          'title' => 'Delete canned reply',
          'method' => 'del',
          'route' => '/api/canned-replies/{uuid}',
          'request' => [
              'curl' => "curl -X DELETE \n {{base_url}}/api/canned-replies/{uuid} \n &nbsp; -H 'Authorization: Bearer your-bearer-token' \n &nbsp; -H 'Content-Type: application/json'",
              'php' => "<?php\n\$client = new Client();\n\$request = new Request('DELETE', '{{base_url}}/api/canned-replies/{{uuid}}');\n\$res = \$client->sendAsync(\$request)->wait();\n\$responseBody = \$res->getBody();\necho \$responseBody;\n?>",
              'nodejs' => "var axios = require('axios');\n\nvar config = {\n  method: 'delete',\n  maxBodyLength: Infinity,\n  url: '{{base_url}}/api/canned-replies/{{id}}',\n  headers: { }\n};\n\naxios(config)\n  .then(function (response) {\n    console.log(JSON.stringify(response.data));\n  })\n  .catch(function (error) {\n    console.log(error);\n  });",
              'python' => "import requests\n\nurl = \"{{base_url}}/api/canned-replies/{{id}}\"\n\npayload={}\nheaders = {}\n\nresponse = requests.request(\"DELETE\", url, headers=headers, data=payload)\n\nprint(response.text)",
              'java' => "OkHttpClient client = new OkHttpClient().newBuilder()\n  .build();\nMediaType mediaType = MediaType.parse(\"text/plain\");\nRequestBody body = RequestBody.create(mediaType, \"\");\nRequest request = new Request.Builder()\n  .url(\"{{base_url}}/api/canned-replies/{{id}}\")\n  .method(\"DELETE\", body)\n  .build();\nResponse response = client.newCall(request).execute();",
              'ruby' => "require \"uri\"\nrequire \"net/http\"\n\nurl = URI(\"{{base_url}}/api/canned-replies/{{id}}\")\n\nhttp = Net::HTTP.new(url.host, url.port);\nrequest = Net::HTTP::Delete.new(url)\n\nresponse = http.request(request)\nputs response.read_body",
          ]
        ],
      ],
    ],
    [
      'title' => 'Messages',
      'value' => [
        [
          'title' => 'Send message',
          'method' => 'post',
          'route' => '/api/send',
          'request' => [
              'curl' => "curl -X POST \n {{base_url}}/api/canned-replies \n &nbsp; -H 'Authorization: Bearer your-bearer-token' \n &nbsp; -H 'Content-Type: application/json'",
              'php' => "<?php\n\$client = new Client();\n\$body = '{\n    \"phone\": \"+1 (968) 082-5846\",\n    \"message\": \"Hello John, how are you?\"\n}';\n\$request = new Request('POST', '{{base_url}}/api/send', [], \$body);\n\$res = \$client->sendAsync(\$request)->wait();\n\$responseBody = \$res->getBody();\necho \$responseBody;\n?>",
              'nodejs' => "var axios = require('axios');\nvar data = '{\n    \"phone\": \"+1 (968) 082-5846\",\n    \"message\": \"Hello John, how are you?\"\n}';\n\nvar config = {\n  method: 'post',\n  maxBodyLength: Infinity,\n  url: '{{base_url}}/api/send',\n  headers: { },\n  data: data\n};\n\naxios(config)\n  .then(function (response) {\n    console.log(JSON.stringify(response.data));\n  })\n  .catch(function (error) {\n    console.log(error);\n  });",
              'python' => "import requests\n\nurl = \"{{base_url}}/api/send\"\n\npayload = \"{\\n    \\\"phone\\\": \\\"+1 (968) 082-5846\\\",\\n    \\\"message\\\": \\\"Hello John, how are you?\\\"\\n}\"\nheaders = {}\n\nresponse = requests.request(\"POST\", url, headers=headers, data=payload)\n\nprint(response.text)",
              'java' => "OkHttpClient client = new OkHttpClient().newBuilder()\n  .build();\nMediaType mediaType = MediaType.parse(\"text/plain\");\nRequestBody body = RequestBody.create(mediaType, \"{\\n    \\\"phone\\\": \\\"+1 (968) 082-5846\\\",\\n    \\\"message\\\": \\\"Hello John, how are you?\\\"\\n}\");\nRequest request = new Request.Builder()\n  .url(\"{{base_url}}/api/send\")\n  .method(\"POST\", body)\n  .build();\nResponse response = client.newCall(request).execute();",
              'ruby' => "require \"uri\"\nrequire \"net/http\"\n\nurl = URI(\"{{base_url}}/api/send\")\n\nhttp = Net::HTTP.new(url.host, url.port);\nrequest = Net::HTTP::Post.new(url)\nrequest.body = \"{\\n    \\\"phone\\\": \\\"+1 (968) 082-5846\\\",\\n    \\\"message\\\": \\\"Hello John, how are you?\\\"\\n}\"\n\nresponse = http.request(request)\nputs response.read_body",
          ]
        ],
      ],
    ],
];