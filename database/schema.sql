-- Fresh installation only; never drops existing data.
CREATE TABLE diagnostic_nodes (
 topic VARCHAR(40) NOT NULL,
 id VARCHAR(40) NOT NULL,
 kind ENUM('question','result') NOT NULL,
 body TEXT NOT NULL,
 explanation TEXT NOT NULL,
 PRIMARY KEY(topic,id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
CREATE TABLE diagnostic_choices (
 topic VARCHAR(40) NOT NULL,
 question_id VARCHAR(40) NOT NULL,
 answer VARCHAR(20) NOT NULL,
 next_id VARCHAR(40) NOT NULL,
 position TINYINT UNSIGNED NOT NULL,
 PRIMARY KEY(topic,question_id,answer),
 FOREIGN KEY(topic,question_id) REFERENCES diagnostic_nodes(topic,id),
 FOREIGN KEY(topic,next_id) REFERENCES diagnostic_nodes(topic,id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
