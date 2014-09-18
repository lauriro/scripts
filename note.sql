


ts TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP 
                                                      # Auto-initialization and auto-update
ts TIMESTAMP DEFAULT CURRENT_TIMESTAMP                # Auto-initialization only
ts TIMESTAMP DEFAULT 0 ON UPDATE CURRENT_TIMESTAMP    # Auto-update only
ts TIMESTAMP DEFAULT 0                                # Neither



INT [UNSIGNED] [ZEROFILL]
TINYINT   (1 bytes) range: -128 to 127 or 0 to 255
SMALLINT  (2 bytes) range: -32768 to 32767 or 0 to 65535
MEDIUMINT (3 bytes) range: -8388608 to 8388607 or 0 to 16777215
INT       (4 bytes) range: -2147483648 to 2147483647 or 0 to 4294967295
BIGINT    (8 bytes) range: -9223372036854775808 to 9223372036854775807 or 0 to 18446744073709551615



1
2
4
8
16
32
64
128
256
512
1024
2048
4096
8192
16384
32768
65536
131072
262144
524288
1048576
2097152
4194304
8388608
16777216
33554432
67108864
134217728
268435456
536870912
1073741824
2147483648

