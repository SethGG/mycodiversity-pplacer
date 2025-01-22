# !/bin/bash

query_dir=query_files
chunk_dir=chunks/aligned
regen_trees_dir=regen_trees

mkdir -p $regen_trees_dir
regen_trees_dir_abs="$(realpath $regen_trees_dir)"

RED='\033[0;31m'
NC='\033[0m'

for chunk_file in $chunk_dir/*.fasta; do
    base_name=$(basename "$chunk_file" .fasta)
    base_name_num=${base_name::3}

    if [ -e $regen_trees_dir/RAxML_bestTree.$base_name_num.out ]
    then
        echo -e "\n${RED}CHUNK ALREADY HAS A BEST TREE: $base_name${NC}\n"
        continue
    else
        echo -e "\n${RED}GENERATING TREE FOR CHUNK: $base_name${NC}\n"
        rm $regen_trees_dir/*.${base_name_num}.out
        ../../deps/standard-RAxML-8.2.13/raxmlHPC-PTHREADS-SSE3 -s $chunk_dir/$base_name.fasta -n $base_name_num.out -w $regen_trees_dir_abs -m GTRCAT -p 12345 -T 4 -o OUTGROUP
    fi
done
