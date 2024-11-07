-- phpMyAdmin SQL Dump
-- version 5.2.0
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Tempo de geração: 19-Maio-2024 às 23:48
-- Versão do servidor: 10.4.27-MariaDB
-- versão do PHP: 8.2.0

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Banco de dados: `fastpdv`
--

-- --------------------------------------------------------

--
-- Estrutura da tabela `caixa`
--

CREATE TABLE `caixa` (
  `id` int(11) NOT NULL,
  `data_abertura` datetime NOT NULL,
  `data_fechamento` datetime DEFAULT NULL,
  `fundo_de_troco` decimal(10,2) NOT NULL,
  `total_vendas` decimal(10,2) DEFAULT 0.00,
  `usuario_id` int(11) DEFAULT NULL,
  `status` enum('Aberto','Fechado') DEFAULT 'Aberto'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Extraindo dados da tabela `caixa`
--

INSERT INTO `caixa` (`id`, `data_abertura`, `data_fechamento`, `fundo_de_troco`, `total_vendas`, `usuario_id`, `status`) VALUES
(4, '2024-05-19 18:23:50', NULL, '10.00', '0.00', 5, 'Aberto');

-- --------------------------------------------------------

--
-- Estrutura da tabela `categorias`
--

CREATE TABLE `categorias` (
  `id` int(11) NOT NULL,
  `nome` varchar(100) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Extraindo dados da tabela `categorias`
--

INSERT INTO `categorias` (`id`, `nome`, `created_at`) VALUES
(1, 'Material Escolar', '2024-05-19 12:47:31'),
(2, 'Uniformes', '2024-05-19 13:25:11');

-- --------------------------------------------------------

--
-- Estrutura da tabela `configuracoes_loja`
--

CREATE TABLE `configuracoes_loja` (
  `id` int(11) NOT NULL,
  `logo` varchar(255) DEFAULT NULL,
  `cor_texto_menu` varchar(7) DEFAULT '#000000',
  `cor_div_menu` varchar(7) DEFAULT '#ffffff',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `nome_loja` varchar(255) DEFAULT 'Minha Loja',
  `endereco` varchar(255) NOT NULL,
  `cnpj` varchar(20) DEFAULT '**.***.***/****-**',
  `ie` varchar(20) DEFAULT '',
  `telefone` varchar(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Extraindo dados da tabela `configuracoes_loja`
--

INSERT INTO `configuracoes_loja` (`id`, `logo`, `cor_texto_menu`, `cor_div_menu`, `created_at`, `nome_loja`, `endereco`, `cnpj`, `ie`, `telefone`) VALUES
(1, 'uploads/6649f826e8466.png', '#000000', '#ccd7e0', '2024-05-19 13:00:15', 'FastPDV', 'Rua Antonio forlenza, 192 Parque Fenanda Cep 05886-10', '41909795000135', '4545445', '11964718868');

-- --------------------------------------------------------

--
-- Estrutura da tabela `itens_venda`
--

CREATE TABLE `itens_venda` (
  `id` int(11) NOT NULL,
  `venda_id` int(11) NOT NULL,
  `produto_id` int(11) NOT NULL,
  `quantidade` int(11) NOT NULL,
  `preco` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estrutura da tabela `itens_venda_cancelados`
--

CREATE TABLE `itens_venda_cancelados` (
  `id` int(11) NOT NULL,
  `venda_cancelada_id` int(11) NOT NULL,
  `produto_id` int(11) NOT NULL,
  `quantidade` int(11) NOT NULL,
  `preco` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Extraindo dados da tabela `itens_venda_cancelados`
--

INSERT INTO `itens_venda_cancelados` (`id`, `venda_cancelada_id`, `produto_id`, `quantidade`, `preco`) VALUES
(1, 1, 1, 1, '10.00'),
(2, 1, 1, 1, '10.00'),
(3, 2, 3, 1, '45.00'),
(4, 2, 3, 1, '45.00'),
(5, 2, 3, 1, '45.00'),
(6, 3, 2, 1, '1.95'),
(7, 3, 2, 1, '1.95'),
(8, 4, 2, 1, '1.95'),
(9, 4, 1, 1, '10.00'),
(10, 5, 3, 1, '45.00'),
(11, 5, 3, 1, '45.00'),
(12, 6, 2, 1, '1.95'),
(13, 7, 2, 1, '1.95'),
(14, 7, 2, 1, '1.95'),
(15, 7, 3, 1, '45.00'),
(16, 8, 2, 1, '1.95'),
(17, 8, 2, 1, '1.95'),
(18, 9, 2, 1, '1.95'),
(19, 9, 3, 1, '45.00'),
(20, 10, 3, 1, '45.00'),
(21, 11, 2, 1, '1.95'),
(22, 11, 3, 1, '45.00');

-- --------------------------------------------------------

--
-- Estrutura da tabela `movimentacoes_caixa`
--

CREATE TABLE `movimentacoes_caixa` (
  `id` int(11) NOT NULL,
  `caixa_id` int(11) DEFAULT NULL,
  `tipo` enum('sangria','suprimento') NOT NULL,
  `valor` decimal(10,2) NOT NULL,
  `descricao` text DEFAULT NULL,
  `data_movimentacao` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estrutura da tabela `produtos`
--

CREATE TABLE `produtos` (
  `id` int(11) NOT NULL,
  `nome` varchar(100) NOT NULL,
  `preco` decimal(10,2) NOT NULL,
  `preco_custo` decimal(10,2) NOT NULL,
  `unidade` enum('unidade','kilograma','centimetros') NOT NULL,
  `categoria_id` int(11) NOT NULL,
  `codigo` varchar(50) NOT NULL,
  `status` enum('ativo','inativo') NOT NULL,
  `visivel_em` enum('PDV','Site','Ambos') NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `estoque` int(11) DEFAULT 0,
  `estoque_minimo` int(11) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Extraindo dados da tabela `produtos`
--

INSERT INTO `produtos` (`id`, `nome`, `preco`, `preco_custo`, `unidade`, `categoria_id`, `codigo`, `status`, `visivel_em`, `created_at`, `estoque`, `estoque_minimo`) VALUES
(1, 'Lapis de cor fabercaste', '10.00', '10.00', 'unidade', 1, '145454', 'ativo', 'Site', '2024-05-19 12:47:56', 3, 0),
(2, 'Apontador', '1.95', '0.50', 'unidade', 1, '5445', 'ativo', 'Ambos', '2024-05-19 12:55:39', 10, 0),
(3, 'Camiseta branca goal v', '45.00', '18.00', 'unidade', 2, '0008636796', 'ativo', 'PDV', '2024-05-19 13:25:40', 9, 0);

-- --------------------------------------------------------

--
-- Estrutura da tabela `usuarios`
--

CREATE TABLE `usuarios` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `nivel_acesso` enum('usuario','admin') NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Extraindo dados da tabela `usuarios`
--

INSERT INTO `usuarios` (`id`, `username`, `password`, `nivel_acesso`, `created_at`) VALUES
(5, 'admin', '$2y$10$xJUZizptdFmNGCgcbRMfauvDPCQoczalnoGOX3.Q8DvherExhX/vS', 'admin', '2024-05-19 12:37:56'),
(6, 'usuario', '$2y$10$hvQ5S3Bn21Q1ORx8/8hDZOJ.CUqTvsfyo0O573dH5409Zb6Tk9g7K', 'usuario', '2024-05-19 12:38:34');

-- --------------------------------------------------------

--
-- Estrutura da tabela `vendas`
--

CREATE TABLE `vendas` (
  `id` int(11) NOT NULL,
  `data_venda` timestamp NOT NULL DEFAULT current_timestamp(),
  `forma_pagamento1` enum('dinheiro','debito','credito','pix') NOT NULL,
  `valor_pagamento1` decimal(10,2) NOT NULL,
  `forma_pagamento2` enum('dinheiro','debito','credito','pix') DEFAULT NULL,
  `valor_pagamento2` decimal(10,2) DEFAULT NULL,
  `total` decimal(10,2) NOT NULL,
  `usuario_id` int(11) NOT NULL,
  `caixa_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estrutura da tabela `vendas_canceladas`
--

CREATE TABLE `vendas_canceladas` (
  `id` int(11) NOT NULL,
  `venda_id` int(11) NOT NULL,
  `data_cancelamento` datetime NOT NULL,
  `cancelado_por` int(11) NOT NULL,
  `data_venda` datetime NOT NULL,
  `forma_pagamento1` varchar(255) NOT NULL,
  `valor_pagamento1` decimal(10,2) NOT NULL,
  `forma_pagamento2` varchar(255) DEFAULT NULL,
  `valor_pagamento2` decimal(10,2) DEFAULT NULL,
  `total` decimal(10,2) NOT NULL,
  `usuario_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Extraindo dados da tabela `vendas_canceladas`
--

INSERT INTO `vendas_canceladas` (`id`, `venda_id`, `data_cancelamento`, `cancelado_por`, `data_venda`, `forma_pagamento1`, `valor_pagamento1`, `forma_pagamento2`, `valor_pagamento2`, `total`, `usuario_id`) VALUES
(1, 5, '2024-05-19 16:23:54', 5, '2024-05-19 15:31:33', 'dinheiro', '10.00', 'debito', '10.00', '20.00', 5),
(2, 4, '2024-05-19 16:23:58', 5, '2024-05-19 15:27:25', 'dinheiro', '100.00', 'debito', '35.05', '135.00', 5),
(3, 3, '2024-05-19 16:44:03', 5, '2024-05-19 10:31:03', 'debito', '5.00', 'credito', '1.00', '3.90', 5),
(4, 1, '2024-05-19 17:27:35', 5, '2024-05-19 10:22:24', 'dinheiro', '12.00', '', '0.00', '11.95', 5),
(5, 17, '2024-05-19 17:41:02', 5, '2024-05-19 17:27:25', 'dinheiro', '50.00', 'debito', '40.00', '90.00', 5),
(6, 16, '2024-05-19 17:41:05', 5, '2024-05-19 17:23:20', 'dinheiro', '11.11', '', '0.00', '1.95', 5),
(7, 26, '2024-05-19 18:32:40', 5, '2024-05-19 18:30:01', 'dinheiro', '48.90', 'credito', '0.00', '48.90', 5),
(8, 25, '2024-05-19 18:32:52', 5, '2024-05-19 18:29:21', 'dinheiro', '2.00', 'debito', '1.90', '3.90', 5),
(9, 27, '2024-05-19 18:44:39', 5, '2024-05-19 18:35:00', 'dinheiro', '10.00', 'debito', '36.95', '46.95', 5),
(10, 29, '2024-05-19 18:44:41', 5, '2024-05-19 18:43:40', 'dinheiro', '45.00', '', '0.00', '45.00', 5),
(11, 28, '2024-05-19 18:44:43', 5, '2024-05-19 18:41:03', 'debito', '10.00', 'dinheiro', '36.95', '46.95', 5);

--
-- Índices para tabelas despejadas
--

--
-- Índices para tabela `caixa`
--
ALTER TABLE `caixa`
  ADD PRIMARY KEY (`id`),
  ADD KEY `usuario_id` (`usuario_id`);

--
-- Índices para tabela `categorias`
--
ALTER TABLE `categorias`
  ADD PRIMARY KEY (`id`);

--
-- Índices para tabela `configuracoes_loja`
--
ALTER TABLE `configuracoes_loja`
  ADD PRIMARY KEY (`id`);

--
-- Índices para tabela `itens_venda`
--
ALTER TABLE `itens_venda`
  ADD PRIMARY KEY (`id`),
  ADD KEY `venda_id` (`venda_id`),
  ADD KEY `produto_id` (`produto_id`);

--
-- Índices para tabela `itens_venda_cancelados`
--
ALTER TABLE `itens_venda_cancelados`
  ADD PRIMARY KEY (`id`);

--
-- Índices para tabela `movimentacoes_caixa`
--
ALTER TABLE `movimentacoes_caixa`
  ADD PRIMARY KEY (`id`),
  ADD KEY `caixa_id` (`caixa_id`);

--
-- Índices para tabela `produtos`
--
ALTER TABLE `produtos`
  ADD PRIMARY KEY (`id`),
  ADD KEY `categoria_id` (`categoria_id`);

--
-- Índices para tabela `usuarios`
--
ALTER TABLE `usuarios`
  ADD PRIMARY KEY (`id`);

--
-- Índices para tabela `vendas`
--
ALTER TABLE `vendas`
  ADD PRIMARY KEY (`id`);

--
-- Índices para tabela `vendas_canceladas`
--
ALTER TABLE `vendas_canceladas`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT de tabelas despejadas
--

--
-- AUTO_INCREMENT de tabela `caixa`
--
ALTER TABLE `caixa`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT de tabela `categorias`
--
ALTER TABLE `categorias`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de tabela `configuracoes_loja`
--
ALTER TABLE `configuracoes_loja`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de tabela `itens_venda`
--
ALTER TABLE `itens_venda`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=56;

--
-- AUTO_INCREMENT de tabela `itens_venda_cancelados`
--
ALTER TABLE `itens_venda_cancelados`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=23;

--
-- AUTO_INCREMENT de tabela `movimentacoes_caixa`
--
ALTER TABLE `movimentacoes_caixa`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT de tabela `produtos`
--
ALTER TABLE `produtos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT de tabela `usuarios`
--
ALTER TABLE `usuarios`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT de tabela `vendas`
--
ALTER TABLE `vendas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=30;

--
-- AUTO_INCREMENT de tabela `vendas_canceladas`
--
ALTER TABLE `vendas_canceladas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- Restrições para despejos de tabelas
--

--
-- Limitadores para a tabela `caixa`
--
ALTER TABLE `caixa`
  ADD CONSTRAINT `caixa_ibfk_1` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`);

--
-- Limitadores para a tabela `itens_venda`
--
ALTER TABLE `itens_venda`
  ADD CONSTRAINT `itens_venda_ibfk_1` FOREIGN KEY (`venda_id`) REFERENCES `vendas` (`id`),
  ADD CONSTRAINT `itens_venda_ibfk_2` FOREIGN KEY (`produto_id`) REFERENCES `produtos` (`id`);

--
-- Limitadores para a tabela `movimentacoes_caixa`
--
ALTER TABLE `movimentacoes_caixa`
  ADD CONSTRAINT `movimentacoes_caixa_ibfk_1` FOREIGN KEY (`caixa_id`) REFERENCES `caixa` (`id`);

--
-- Limitadores para a tabela `produtos`
--
ALTER TABLE `produtos`
  ADD CONSTRAINT `produtos_ibfk_1` FOREIGN KEY (`categoria_id`) REFERENCES `categorias` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
