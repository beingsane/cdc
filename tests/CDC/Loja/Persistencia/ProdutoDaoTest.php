<?php

namespace CDC\Loja\Persistencia;

use CDC\Loja\Test\TestCase,
    // CDC\Loja\Persistencia\ConexaoComBancoDeDados,
    CDC\Loja\Persistencia\ProdutoDao,
    CDC\Loja\Produto\Produto;

use PDO;
use Mockery;

class ProdutoDaoTest extends TestCase
{
    private $conexao;

    protected function setUp()
    {
        parent::setUp();

        $this->conexao = new PDO('sqlite:test.db');

        $this->conexao->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $this->criarTabela();
    }

    protected function criarTabela()
    {
        $sqlString = "CREATE TABLE produto ";
        $sqlString .= "(id INTEGER PRIMARY KEY, descricao TEXT, ";
        $sqlString .= "valor_unitario TEXT, status TINYINT(1));";

        $this->conexao->query($sqlString);
    }

    public function testDeveAdicionarUmProduto()
    {
        $produtoDao = new ProdutoDao($this->conexao);

        $produto = new Produto('Geladeira', 150.0);

        // Sobrescrevendo a conexão para continuar trabalhando
        // sobre a mesma já instanciada
        $conexao = $produtoDao->adiciona($produto);

        // buscando pelo id para
        // ver se está igual o produto do cenário
        $salvo = $produtoDao->porId($conexao->lastInsertId());

        $this->assertEquals($salvo["descricao"], $produto->getNome());
        $this->assertEquals($salvo["valor_unitario"], $produto->getValorUnitario());
        $this->assertEquals($salvo["status"], $produto->getStatus());
    }

    public function testDeveFiltrarAtivos()
    {
        $produtoDao = new ProdutoDao($this->conexao);

        $ativo = new Produto('Geladeira', 150.0, 1);
        $inativo = new Produto('Geladeira', 180.0, 1, false);
        $inativo->inativa();

        $produtoDao->adiciona($ativo);
        $produtoDao->adiciona($inativo);

        $produtosAtivos = $produtoDao->ativos();

        $this->assertEquals(1, count($produtosAtivos));
        $this->assertEquals(150.0, $produtosAtivos[0]['valor_unitario']);
    }

    public function testPorId()
    {
        $conexao = Mockery::mock('PDO');
        $conexao->shouldReceive('query')->andReturn($conexao);
        $conexao->shouldReceive('fetch')->andReturn(array('id' => 1));

        $produtoDao = new ProdutoDao($conexao);
        $this->assertNotNull($produtoDao->porId(1));
    }

    protected function tearDown()
    {
        parent::tearDown();
        unlink('test.db');
    }
}
