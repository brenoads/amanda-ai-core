# Amanda AI Core

*Read this in other languages: [English](#english-version)*

O **Amanda AI Core** é um núcleo cognitivo de assistente virtual desenvolvido para operar **100% offline**, utilizando princípios de **Edge Computing**, processamento local e **Privacy-by-Design**.

O projeto combina **Python, PHP e MySQL**, com componentes preparados para futura integração de módulos de baixo nível em **C++**.

A arquitetura atual foi projetada para executar reconhecimento de fala, processamento de comandos, recuperação de conhecimento, automação do sistema operacional, cálculos, informações de data e hora e síntese de voz diretamente na máquina local.

> **Status:** Protótipo Experimental / Em Desenvolvimento
> O Amanda AI Core está em desenvolvimento ativo. A arquitetura atual representa uma base funcional para evolução do sistema cognitivo, aprendizado supervisionado e integração futura com outros projetos de IA e robótica.

---

## Visão Geral

O Amanda AI Core não depende de uma API de IA em nuvem para seu funcionamento principal.

O fluxo atual utiliza:

```text
Microfone
    │
    ▼
Vosk STT
    │
    ▼
Python Listener
    │
    ▼
Amanda Core
    │
    ▼
PHP Cognitive Core
    │
    ├── Normalização fonética
    ├── Aprendizado supervisionado
    ├── Automação do sistema operacional
    ├── Data / Hora
    ├── Matemática
    └── Recuperação de conhecimento
            │
            ▼
        MySQL
            │
            ▼
      Resposta textual
            │
            ▼
        Piper TTS
            │
            ▼
       Voz da Amanda
```

---

## Arquitetura Atual

O sistema é dividido principalmente em três camadas:

### 1. Camada de Percepção — Python

O Python é responsável pela captura e reconhecimento da fala.

O sistema utiliza:

* `sounddevice` para captura do microfone
* `Vosk` para reconhecimento de fala offline
* Modelo acústico PT-BR local
* Processamento contínuo de áudio
* Detecção de comandos por palavra-chave

O áudio é capturado em **16 kHz, mono**, e enviado ao reconhecedor Vosk em tempo real.

Quando o Vosk identifica o final de uma expressão, o texto reconhecido é enviado ao núcleo cognitivo.

---

### 2. Camada Cognitiva — PHP

O arquivo `brain.php` funciona como o núcleo de processamento de comandos.

Ele recebe o texto reconhecido pelo Python e executa uma sequência de módulos especializados.

Entre eles:

* Normalização de texto
* Normalização fonética
* Máquina de estados para aprendizado
* Automação do sistema operacional
* Consulta de data e hora
* Operações matemáticas
* Recuperação de conhecimento no banco de dados
* Fallback para novos conhecimentos

O núcleo também mantém o estado de determinadas interações em um arquivo local:

```text
/tmp/amanda_state.json
```

---

### 3. Camada de Resposta — Piper TTS

Depois que o núcleo PHP produz uma resposta, o Python utiliza **Piper TTS** para transformar o texto em voz.

O fluxo é:

```text
Resposta PHP
     │
     ▼
Limpeza do texto
     │
     ▼
Piper TTS
     │
     ▼
Arquivo WAV temporário
     │
     ▼
Reprodução local
```

O sistema procura automaticamente um modelo `.onnx` dentro do diretório de voz configurado e utiliza um modelo padrão caso nenhum arquivo alternativo seja encontrado.

---

# Sistema de Ativação

Amanda possui uma janela de interação baseada em palavra-chave e tempo.

As palavras atualmente utilizadas como gatilho incluem:

```text
amanda
manda
armando
amada
ama
```

O sistema funciona com três situações principais:

### Chamada direta

Quando o usuário fala apenas o nome da assistente:

```text
Usuário:
Amanda

Amanda:
Oi, o que voce esta a precisar?
```

A interação é então aberta para novas perguntas.

### Janela de interação

Após uma chamada válida, Amanda mantém uma janela de aproximadamente **15 segundos** para receber comandos sem exigir que o nome seja repetido.

```text
Amanda
   │
   ├── Ativação
   │
   ├── Janela de interação
   │
   ├── Processamento
   │
   └── Nova interação
```

### Janela de agradecimento

Existe também uma janela de aproximadamente **30 segundos** para reconhecer expressões como:

```text
obrigado
obrigada
```

Nesse caso, Amanda responde:

```text
Por nada!
```

e encerra a sessão atual.

---

# Normalização Fonética

Um dos componentes do núcleo é um middleware simples de normalização destinado a compensar possíveis erros de reconhecimento do Vosk.

O sistema converte determinadas palavras reconhecidas em representações mais adequadas para processamento.

Por exemplo:

```text
zero       → 0
um         → 1
dois       → 2
três       → 3
quatro     → 4
...
mil        → 1000
mais       → +
menos      → -
vezes      → *
dividido   → /
```

Isso permite que comandos de voz sejam convertidos em estruturas que os módulos internos conseguem interpretar.

O `brain.php` também remove acentos durante o processamento para facilitar a comparação das expressões.

---

# Módulo de Aprendizado Supervisionado

Uma das funcionalidades centrais do Amanda AI Core é um mecanismo simples de **aprendizado supervisionado baseado em regras**.

Quando Amanda não encontra uma resposta conhecida, ela pode perguntar:

```text
Ainda não tenho isso na minha base de dados.
Deseja inserir essa informação agora?
```

Caso o usuário confirme, o sistema entra em um estado:

```text
awaiting_answer
```

e solicita:

```text
Por favor, me diga qual é a resposta.
```

A pergunta original e a resposta fornecida são então armazenadas na tabela:

```text
knowledge_base
```

do banco de dados local.

Esse mecanismo permite que a base de conhecimento seja expandida progressivamente através da interação com o usuário.

> **Importante:** o mecanismo atual é um sistema de aprendizado baseado em regras e armazenamento de conhecimento. Ele não representa treinamento de um modelo neural.

---

# Base de Conhecimento

O conhecimento persistente é armazenado em um banco de dados **MySQL**.

A estrutura utilizada pelo núcleo trabalha com:

```text
knowledge_base
├── keyword
└── response
```

Durante uma consulta, o sistema carrega as regras cadastradas e procura correspondências entre as palavras-chave armazenadas e a entrada processada.

As respostas correspondentes são retornadas diretamente para o Python, que posteriormente realiza a síntese de voz.

---

# Cadastro de Conhecimento via Terminal

O projeto possui também uma ferramenta CLI independente:

```text
inserter.php
```

Ela permite adicionar ou atualizar conhecimento diretamente pelo terminal, sem necessidade de uma interface web.

Exemplo conceitual:

```text
============================================
   Amanda AI: Add New Knowledge Rule
============================================

Enter Trigger Keyword:
> quem é você

Enter AI Response:
> Eu sou Amanda, uma assistente virtual local.

[✔] Success: Rule successfully registered
```

A ferramenta foi projetada para funcionar exclusivamente através do **CLI**, recusando execução através de navegador web.

Ela também utiliza uma operação de *upsert*: se a palavra-chave já existir, sua resposta pode ser atualizada.

---

# Automação do Sistema Operacional

Amanda possui um módulo específico para comandos de abertura de programas no Linux.

Atualmente existem comandos mapeados para:

### Google Chrome

```text
Amanda, abra o Chrome.
```

### Calculadora

```text
Amanda, abra a calculadora.
```

### Terminal

```text
Amanda, abra o terminal.
```

### Gerenciador de arquivos

```text
Amanda, abra minhas pastas.
```

O núcleo utiliza comandos locais do sistema para iniciar os aplicativos.

A arquitetura foi concebida para permitir a expansão futura desse módulo com novas ações controladas.

---

# Módulos Nativos

Algumas funções não dependem de banco de dados.

Elas são processadas diretamente pelo núcleo PHP.

## Hora

Amanda pode responder perguntas relacionadas ao horário atual.

```text
Usuário:
Que horas são?

Amanda:
Agora são 10 horas e 30 minutos.
```

## Data

Também existe suporte para consultas relacionadas à data atual.

```text
Usuário:
Que dia é hoje?

Amanda:
Hoje é dia 1 de outubro de 2026.
```

## Matemática

O núcleo possui suporte para operações matemáticas básicas:

```text
+
-
*
/
```

Exemplo:

```text
Usuário:
10 + 5

Amanda:
O resultado é 15.
```

Também existe tratamento específico para divisão por zero.

---

# Tratamento de Erros e Fallback

Quando um comando não possui correspondência na base de conhecimento ou nos módulos nativos, Amanda não simplesmente encerra o processamento.

O sistema cria um estado de aprendizado:

```text
awaiting_confirmation
```

e pergunta ao usuário se ele deseja cadastrar uma resposta.

Isso cria o seguinte fluxo:

```text
Comando desconhecido
        │
        ▼
Consulta ao conhecimento
        │
        ▼
Não encontrado
        │
        ▼
Pergunta ao usuário
        │
        ├── Não
        │    └── Cancela aprendizado
        │
        └── Sim
             │
             ▼
       Solicita resposta
             │
             ▼
       Salva no MySQL
```

---

# Privacy-by-Design

A arquitetura foi desenvolvida com uma abordagem **local-first**.

O processamento principal ocorre na própria máquina:

```text
Microfone
   │
   ▼
Vosk Local
   │
   ▼
Python
   │
   ▼
PHP
   │
   ├── Banco de Dados Local
   ├── Regras
   ├── Automação
   └── Processamento
   │
   ▼
Piper Local
   │
   ▼
Áudio
```

Isso reduz a necessidade de enviar:

* Áudio
* Comandos
* Respostas
* Conhecimento armazenado
* Dados de interação

para serviços externos.

---

# Edge Computing

Amanda AI Core foi projetada seguindo uma abordagem de **Edge Computing**.

Em vez de depender de servidores remotos para cada interação, o processamento é realizado diretamente no computador onde a assistente está instalada.

### Características

* Processamento local
* Reconhecimento de fala offline
* Banco de conhecimento local
* TTS local
* Automação local
* Baixa dependência de rede
* Controle direto do hardware

Essa abordagem também permite que o sistema continue funcionando em ambientes sem conexão com a Internet.

---

# Arquitetura Poliglota

O projeto utiliza diferentes tecnologias de acordo com a responsabilidade de cada camada.

```text
                 Amanda AI Core
                       │
        ┌──────────────┼──────────────┐
        │              │              │
        ▼              ▼              ▼
     Python           PHP           MySQL
        │              │              │
        │              │              │
    Percepção      Cognição       Memória
        │              │              │
        └──────────────┼──────────────┘
                       │
                       ▼
                    Piper
                      TTS
```

### Python

Responsável principalmente por:

* Captura de áudio
* Reconhecimento de fala
* Controle da sessão
* Gerenciamento do fluxo
* Comunicação com o PHP
* Síntese de voz
* Reprodução do áudio

### PHP

Responsável principalmente por:

* Processamento cognitivo
* Normalização
* Regras
* Aprendizado supervisionado
* Banco de conhecimento
* Automação do sistema
* Funções nativas

### MySQL

Responsável por:

* Armazenamento persistente
* Base de conhecimento
* Regras e respostas aprendidas

### C++

A arquitetura do projeto prevê a possibilidade de utilização futura de C++ em componentes que necessitem de maior desempenho ou integração de baixo nível.

---

# Estrutura Conceitual

```text
Amanda AI Core
│
├── Perception
│   └── Vosk
│
├── Orchestration
│   └── Python
│
├── Cognitive Core
│   └── PHP
│       ├── Phonetic Normalization
│       ├── Supervised Learning
│       ├── OS Automation
│       ├── Time / Date
│       ├── Mathematics
│       └── Knowledge Retrieval
│
├── Knowledge
│   └── MySQL
│       └── knowledge_base
│
└── Voice Output
    └── Piper TTS
```

---

# Relação com o Shadow Translator

O **Shadow Translator** e o **Amanda AI Core** podem funcionar como projetos complementares dentro de uma arquitetura maior de inteligência local.

O Shadow Translator é especializado em:

```text
Fala
 ↓
STT
 ↓
Tradução Local
 ↓
TTS
```

Enquanto Amanda possui uma arquitetura mais abrangente:

```text
Fala / Texto
      ↓
  Percepção
      ↓
      NLP
      ↓
Núcleo Cognitivo
      ↓
 ┌────┼────────┬────────┐
 ↓    ↓        ↓        ↓
DB  Aprendizado  OS   Outros
     │             │
     └──────┬──────┘
            ↓
          Ação
```

O Shadow Translator pode futuramente funcionar como um componente especializado de percepção e tradução dentro de um ecossistema maior de IA local.

---

# Segurança e Controle

O Amanda AI Core foi concebido para utilizar ações previamente mapeadas em vez de permitir que qualquer comando arbitrário seja executado diretamente pelo sistema operacional.

Por exemplo:

```text
"abrir chrome"
       │
       ▼
Regra conhecida
       │
       ▼
Google Chrome
```

Isso permite que futuras expansões da automação sejam adicionadas de maneira controlada.

---

# Dependências Principais

A arquitetura atual utiliza:

```text
Python
├── sounddevice
├── vosk
└── subprocess

PHP
└── PDO / MySQL

Speech
├── Vosk
└── Piper TTS

Database
└── MySQL
```

O sistema também depende de componentes do ambiente Linux para reprodução de áudio e execução dos aplicativos locais.

---

# Estado Atual

### Implementado

* [x] Reconhecimento de voz offline com Vosk
* [x] Captura de áudio em tempo real
* [x] Ativação por palavra-chave
* [x] Janela de interação de 15 segundos
* [x] Janela especial para agradecimentos
* [x] Comunicação Python → PHP
* [x] Núcleo cognitivo em PHP
* [x] Normalização de texto
* [x] Middleware de normalização fonética
* [x] Base de conhecimento MySQL
* [x] Recuperação de respostas
* [x] Aprendizado supervisionado baseado em regras
* [x] Cadastro de conhecimento pelo terminal
* [x] Automação de aplicativos Linux
* [x] Consulta de data
* [x] Consulta de hora
* [x] Operações matemáticas básicas
* [x] Síntese de voz local com Piper
* [x] Reprodução local das respostas

### Em desenvolvimento

* [ ] Memória contextual avançada
* [ ] Maior compreensão semântica
* [ ] NLP mais sofisticado
* [ ] Gerenciamento de contexto entre sessões
* [ ] Mais módulos de automação
* [ ] Melhor tratamento de intenções
* [ ] Integração com modelos locais mais avançados
* [ ] Componentes de alto desempenho em C++
* [ ] Integração com robótica
* [ ] Integração com outros projetos de IA local

---

# Filosofia do Projeto

O Amanda AI Core não foi concebido apenas como um chatbot.

A proposta é construir gradualmente um **núcleo cognitivo local**, capaz de conectar:

```text
Percepção
    +
Compreensão
    +
Conhecimento
    +
Memória
    +
Ação
```

Tudo isso executado localmente e organizado em módulos independentes.

A arquitetura permite começar com regras determinísticas e conhecimento estruturado e, posteriormente, incorporar componentes de IA mais avançados sem abandonar o princípio de processamento local.

---

# Roadmap

* [x] Núcleo offline inicial
* [x] Reconhecimento de fala local
* [x] Síntese de voz local
* [x] Base de conhecimento
* [x] Aprendizado supervisionado
* [x] Automação básica do Linux
* [x] Processamento de comandos
* [ ] Memória contextual persistente
* [ ] Motor de intenções mais avançado
* [ ] NLP local avançado
* [ ] Raciocínio contextual
* [ ] Integração de modelos locais
* [ ] Automação expandida
* [ ] Integração com hardware
* [ ] Integração com robótica
* [ ] Módulos C++ de alto desempenho
* [ ] Ecossistema integrado com outros projetos de IA local

---

# English Version

<a id="english-version"></a>

# Amanda AI Core

The **Amanda AI Core** is a virtual assistant cognitive core designed to operate **100% offline**, following **Edge Computing**, local processing, and **Privacy-by-Design** principles.

The project combines **Python, PHP, and MySQL**, with the possibility of introducing low-level **C++** components in the future.

The current architecture performs speech recognition, command processing, knowledge retrieval, operating-system automation, mathematical operations, date and time queries, and local speech synthesis directly on the host machine.

> **Status:** Experimental Prototype / In Development
> Amanda AI Core is under active development. The current architecture provides a functional foundation for future cognitive capabilities, supervised learning, and integration with other AI and robotics projects.

---

## Overview

Amanda does not require a cloud AI API for its core operation.

The current processing pipeline is:

```text
Microphone
    │
    ▼
Vosk STT
    │
    ▼
Python Listener
    │
    ▼
Amanda Core
    │
    ▼
PHP Cognitive Core
    │
    ├── Phonetic normalization
    ├── Supervised learning
    ├── OS automation
    ├── Date / Time
    ├── Mathematics
    └── Knowledge retrieval
            │
            ▼
          MySQL
            │
            ▼
      Text response
            │
            ▼
         Piper TTS
            │
            ▼
       Amanda Voice
```

---

## Current Architecture

The system is mainly divided into three layers.

### 1. Perception Layer — Python

Python handles audio capture and speech recognition.

It uses:

* `sounddevice` for microphone capture
* `Vosk` for offline speech recognition
* A local PT-BR acoustic model
* Real-time audio processing
* Wake-word detection

The microphone stream runs at **16 kHz, mono**, and audio is continuously passed to Vosk.

When Vosk detects the end of a spoken phrase, the recognized text is forwarded to the cognitive core.

---

### 2. Cognitive Layer — PHP

The `brain.php` file acts as the cognitive processing core.

It receives recognized text from Python and executes specialized modules:

* Text normalization
* Phonetic normalization
* Supervised learning state machine
* Operating-system automation
* Date and time
* Mathematical operations
* Knowledge-base retrieval
* Unknown-command fallback

The core also maintains interaction state through:

```text
/tmp/amanda_state.json
```

---

### 3. Response Layer — Piper TTS

After the PHP core generates a response, Python uses **Piper TTS** to convert the response into speech.

```text
PHP Response
     │
     ▼
Text Cleaning
     │
     ▼
Piper TTS
     │
     ▼
Temporary WAV
     │
     ▼
Local Playback
```

The system searches for an `.onnx` voice model in the configured voice directory and falls back to a default model when necessary.

---

# Activation System

Amanda uses a wake-word and time-window based interaction model.

Current trigger variations include:

```text
amanda
manda
armando
amada
ama
```

### Direct Activation

When the user says only the assistant's name:

```text
User:
Amanda

Amanda:
Oi, o que voce esta a precisar?
```

Amanda opens an interaction window.

### Interaction Window

After activation, Amanda keeps a conversation window open for approximately **15 seconds**.

```text
Amanda
   │
   ├── Activation
   │
   ├── Interaction window
   │
   ├── Processing
   │
   └── Next interaction
```

### Gratitude Window

A separate approximately **30-second** window recognizes:

```text
obrigado
obrigada
```

and responds:

```text
Por nada!
```

The current interaction is then closed.

---

# Phonetic Normalization

The cognitive core contains a lightweight middleware designed to compensate for possible Vosk recognition variations.

Examples include:

```text
zero       → 0
um         → 1
dois       → 2
três       → 3
quatro     → 4
...
mil        → 1000
mais       → +
menos      → -
vezes      → *
dividido   → /
```

This allows spoken commands to be converted into representations that internal modules can process.

The core also removes accents during command processing to simplify matching.

---

# Supervised Learning System

One of Amanda's current capabilities is a simple **rule-based supervised learning mechanism**.

When Amanda cannot find an answer, it can ask:

```text
Ainda não tenho isso na minha base de dados.
Deseja inserir essa informação agora?
```

If the user confirms, the system enters:

```text
awaiting_answer
```

and asks:

```text
Por favor, me diga qual é a resposta.
```

The original question and the supplied answer are then stored in the local:

```text
knowledge_base
```

MySQL table.

This allows Amanda's knowledge base to be progressively expanded through interaction.

> **Important:** the current mechanism is a rule-based knowledge-learning system. It does not represent neural model training.

---

# Knowledge Base

Persistent knowledge is stored in a **MySQL** database.

The current structure uses:

```text
knowledge_base
├── keyword
└── response
```

During a query, the core loads the registered rules and searches for keyword matches against the processed user input.

Matching responses are returned to Python and converted into speech through Piper.

---

# CLI Knowledge Registration

The project also includes an independent CLI utility:

```text
inserter.php
```

It allows new rules to be added or updated directly from the terminal without requiring a web interface.

Conceptually:

```text
============================================
   Amanda AI: Add New Knowledge Rule
============================================

Enter Trigger Keyword:
> who are you

Enter AI Response:
> I am Amanda, a local virtual assistant.

[✔] Success: Rule successfully registered
```

The utility is explicitly restricted to CLI execution and refuses execution through a web browser.

It also supports an upsert-style operation, allowing an existing keyword's response to be updated.

---

# Operating System Automation

Amanda contains a dedicated module for opening Linux applications.

Currently mapped applications include:

### Google Chrome

```text
Amanda, abra o Chrome.
```

### Calculator

```text
Amanda, abra a calculadora.
```

### Terminal

```text
Amanda, abra o terminal.
```

### File Manager

```text
Amanda, abra minhas pastas.
```

The PHP core launches these applications through local Linux commands.

The architecture allows additional controlled actions to be added in the future.

---

# Native Modules

Some capabilities do not require the knowledge database.

They are processed directly by the PHP core.

## Time

Amanda can answer current-time queries.

```text
User:
Que horas são?

Amanda:
Agora são 10 horas e 30 minutos.
```

## Date

Amanda also supports current-date queries.

```text
User:
Que dia é hoje?

Amanda:
Hoje é dia 1 de outubro de 2026.
```

## Mathematics

The core currently supports basic:

```text
+
-
*
/
```

Example:

```text
User:
10 + 5

Amanda:
O resultado é 15.
```

Division by zero is explicitly handled.

---

# Error Handling and Fallback

When a command does not match the knowledge base or native modules, Amanda enters a learning workflow.

```text
Unknown command
       │
       ▼
Knowledge lookup
       │
       ▼
Not found
       │
       ▼
Ask user
       │
       ├── No
       │    └── Cancel learning
       │
       └── Yes
            │
            ▼
       Request answer
            │
            ▼
       Save to MySQL
```

This creates a simple mechanism through which Amanda can expand its local knowledge.

---

# Privacy-by-Design

Amanda follows a **local-first** architecture.

The main processing pipeline remains on the local machine:

```text
Microphone
   │
   ▼
Local Vosk
   │
   ▼
Python
   │
   ▼
PHP
   │
   ├── Local Database
   ├── Rules
   ├── Automation
   └── Processing
   │
   ▼
Local Piper
   │
   ▼
Audio
```

This reduces the need to transmit:

* Audio
* Commands
* Responses
* Stored knowledge
* Interaction data

to external services.

---

# Edge Computing

Amanda AI Core follows an **Edge Computing** approach.

Instead of depending on remote servers for every interaction, processing occurs directly on the computer running the assistant.

### Characteristics

* Local processing
* Offline speech recognition
* Local knowledge database
* Local TTS
* Local automation
* Low network dependency
* Direct hardware access

This allows the system to continue operating in environments without Internet connectivity.

---

# Polyglot Architecture

The project uses different technologies according to the responsibility of each layer.

```text
                 Amanda AI Core
                       │
        ┌──────────────┼──────────────┐
        │              │              │
        ▼              ▼              ▼
     Python           PHP           MySQL
        │              │              │
        │              │              │
    Perception      Cognition       Memory
        │              │              │
        └──────────────┼──────────────┘
                       │
                       ▼
                    Piper
                      TTS
```

### Python

Responsible mainly for:

* Audio capture
* Speech recognition
* Session control
* Flow management
* Python → PHP communication
* Speech synthesis
* Audio playback

### PHP

Responsible mainly for:

* Cognitive processing
* Normalization
* Rules
* Supervised learning
* Knowledge base
* OS automation
* Native functions

### MySQL

Responsible for:

* Persistent storage
* Knowledge base
* Learned rules and responses

### C++

The architecture allows future C++ components where higher performance or lower-level hardware integration is required.

---

# Conceptual Structure

```text
Amanda AI Core
│
├── Perception
│   └── Vosk
│
├── Orchestration
│   └── Python
│
├── Cognitive Core
│   └── PHP
│       ├── Phonetic Normalization
│       ├── Supervised Learning
│       ├── OS Automation
│       ├── Time / Date
│       ├── Mathematics
│       └── Knowledge Retrieval
│
├── Knowledge
│   └── MySQL
│       └── knowledge_base
│
└── Voice Output
    └── Piper TTS
```

---

# Relationship with Shadow Translator

**Shadow Translator** and **Amanda AI Core** can operate as complementary projects within a broader local AI architecture.

Shadow Translator specializes in:

```text
Speech
 ↓
STT
 ↓
Local Translation
 ↓
TTS
```

Amanda provides a broader architecture:

```text
Speech / Text
      ↓
  Perception
      ↓
     NLP
      ↓
 Cognitive Core
      ↓
 ┌────┼────────┬────────┐
 ↓    ↓        ↓        ↓
DB  Learning   OS     Other
     │             │
     └──────┬──────┘
            ↓
          Action
```

Shadow Translator can potentially become a specialized perception or translation component within a larger local AI ecosystem.

---

# Security and Control

Amanda is designed around predefined and controlled system actions rather than unrestricted arbitrary command execution.

For example:

```text
"abrir chrome"
       │
       ▼
Known Rule
       │
       ▼
Google Chrome
```

This makes it possible to expand OS automation while maintaining explicit mappings between recognized commands and system actions.

---

# Main Dependencies

The current architecture uses:

```text
Python
├── sounddevice
├── vosk
└── subprocess

PHP
└── PDO / MySQL

Speech
├── Vosk
└── Piper TTS

Database
└── MySQL
```

The system also depends on Linux components for local audio playback and application execution.

---

# Current Status

### Implemented

* [x] Offline speech recognition with Vosk
* [x] Real-time microphone capture
* [x] Wake-word activation
* [x] 15-second interaction window
* [x] Dedicated gratitude window
* [x] Python → PHP communication
* [x] PHP cognitive core
* [x] Text normalization
* [x] Phonetic normalization middleware
* [x] MySQL knowledge base
* [x] Knowledge retrieval
* [x] Rule-based supervised learning
* [x] CLI knowledge registration
* [x] Linux application automation
* [x] Date queries
* [x] Time queries
* [x] Basic mathematics
* [x] Local Piper speech synthesis
* [x] Local audio playback

### In Development

* [ ] Advanced contextual memory
* [ ] Greater semantic understanding
* [ ] More sophisticated NLP
* [ ] Cross-session context management
* [ ] Additional automation modules
* [ ] Improved intent handling
* [ ] Integration with more advanced local models
* [ ] High-performance C++ components
* [ ] Robotics integration
* [ ] Integration with other local AI projects

---

# Project Philosophy

Amanda AI Core is not intended to be just another chatbot.

The long-term objective is to progressively build a **local cognitive core** capable of connecting:

```text
Perception
    +
Understanding
    +
Knowledge
    +
Memory
    +
Action
```

All of these capabilities are organized into independent modules and designed to operate locally.

The architecture can begin with deterministic rules and structured knowledge while progressively incorporating more advanced AI components without abandoning the local-processing principle.

---

# Roadmap

* [x] Initial offline core
* [x] Local speech recognition
* [x] Local speech synthesis
* [x] Knowledge base
* [x] Supervised learning
* [x] Basic Linux automation
* [x] Command processing
* [ ] Persistent contextual memory
* [ ] Advanced intent engine
* [ ] Advanced local NLP
* [ ] Contextual reasoning
* [ ] Local model integration
* [ ] Expanded automation
* [ ] Hardware integration
* [ ] Robotics integration
* [ ] High-performance C++ modules
* [ ] Integration with other local AI projects

---

# Technology Stack

```text
Languages
├── Python
├── PHP
└── C++

AI / Speech
├── Vosk
└── Piper TTS

Database
└── MySQL

Architecture
├── Edge Computing
├── Local AI
├── Offline NLP
├── Rule-Based Learning
└── Privacy-by-Design
```

---

# Project Goals

Amanda AI Core aims to evolve into a **local cognitive platform** capable of:

* Understanding natural language
* Processing speech locally
* Accessing structured information
* Learning new rule-based knowledge
* Maintaining contextual information
* Executing controlled local actions
* Interacting with hardware
* Operating without permanent Internet access

The objective is to move beyond a traditional chatbot architecture toward a **modular, local, autonomous, and hardware-aware AI system**.
