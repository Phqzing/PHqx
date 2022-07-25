-- #! sqlite


-- #{ phqx

-- #    { init
CREATE TABLE IF NOT EXISTS settings (
    name VARCHAR(16) NOT NULL PRIMARY KEY,
    killaura TEXT,
    reach TEXT,
    speed TEXT,
    automessage TEXT,
    antikb BOOLEAN,
    phase BOOLEAN,
    taptotp BOOLEAN,
    cheststealer BOOLEAN
);
-- #    }


-- #    { check
-- #    :column string
SELECT COUNT(*) AS CNTREC FROM pragma_table_info("settings") WHERE name = :column;
-- #    }

-- #    { addcolumn
-- #        { automessage
ALTER TABLE settings ADD COLUMN automessage TEXT;
-- #        }
-- #        { phase
ALTER TABLE settings ADD COLUMN phase BOOLEAN;
-- #        }
-- #        { taptotp
ALTER TABLE settings ADD COLUMN taptotp BOOLEAN;
-- #        }
-- #        { cheststealer
ALTER TABLE settings ADD COLUMN cheststealer BOOLEAN;
-- #        }
-- #    }

-- #    { insert
-- #    :name string
INSERT OR REPLACE INTO settings
(name, killaura, reach, speed, automessage, antikb, phase, taptotp, cheststealer)
VALUES
(:name, "none", "none", "none", "none", false, false, false, false);
-- #    }


-- #    { get
-- #    :name string
SELECT * FROM settings WHERE name = :name;
-- #    }


-- #    { save
-- #    :name string
-- #    :killaura string
-- #    :reach string
-- #    :speed string
-- #    :automessage string
-- #    :antikb bool
-- #    :phase bool
-- #    :taptotp bool
-- #    :cheststealer bool
UPDATE settings SET killaura = :killaura, reach = :reach, speed = :speed, automessage = :automessage, antikb = :antikb, phase = :phase, taptotp = :taptotp, cheststealer = :cheststealer WHERE name = :name;
-- #    }

-- #}