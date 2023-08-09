# data-repo
Ferramenta para manipular dados de várias origens usando SQL.

**data-repo** usa PHP PDO::SQLite para fornecer uma forma simples e rápida para manipular dados tabulares (linhas/colunas) oriundos de diversas fontes, usando SQL.

Em linhas gerais:

- Cada instância de `DataRepo` corresponde aum uma conexão a um banco de dados SQLite na memória (isso pode ser mudado para arquivo);

- Tabelas podem ser criadas sem utilizar SQL, apenas utilizando-se das classes `Table` e `Field`;

- Todas as operações das classes `PDO` e `PDOStatement` estão disponíveis;

- A leitura/gravação de/para diversos formatos está disponível (MS Excel, CSV, SQLite, etc).

