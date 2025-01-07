#!/bin/bash

# Set Python environment for taxtastic
source deps/venv/bin/activate

# Input and directory setup
input_fasta=$1
input_dir=$(dirname "$1")
output_dir=$input_dir/placement_output
chunk_agg_csv=$input_dir/blast_hits_chunks_agg.csv

phylogeny_dir=MDDB-phylogeny/l0.2_s3_4_1500_o1.0_a0_constr_localpair
chunk_align_dir=$phylogeny_dir/chunks/aligned
chunk_tree_dir=$phylogeny_dir/regen_trees

# Create output directory for placement results
mkdir -p "$output_dir"

echo "Creating chunk-specific placement directories and FASTA files"

# Extract sequences per chunk
awk -F',' 'NR > 1 {print $2 "," $1}' "$chunk_agg_csv" | while IFS=',' read -r chunk seq_id; do
    # Create a directory for the chunk if it doesn't exist
    mkdir -p "$output_dir/$chunk"
    
    # Append sequence ID to a chunk-specific text file
    echo "$seq_id" >> "$output_dir/$chunk/seq_ids.txt"
done

# Perform phylogenetic placement for each chunk
for chunk_dir in "$output_dir"/*; do
    seq_file="$chunk_dir/seq_ids.txt"
    output_fasta="$chunk_dir/placed_seqs.fasta"
    output_refpkg="$chunk_dir/refpkg.refpkg"
    output_align="$chunk_dir/placed_seqs_aligned.fasta"
    output_pplacer="$chunk_dir/placements.jplace"

    mafft_log="$chunk_dir/mafft.log"
    pplacer_log="$chunk_dir/pplacer.log"

    chunk_base_name=$(basename "$chunk_dir")
    chunk_base_name_num=${chunk_base_name::3}

    chunk_align="$chunk_align_dir/$chunk_base_name.fasta"
    chunk_tree_stats="$chunk_tree_dir/RAxML_info.$chunk_base_name_num.out"
    chunk_tree="$chunk_tree_dir/RAxML_bestTree.$chunk_base_name_num.out"
    
    echo ""
    echo "Processing Chunk: $chunk_base_name"
    echo "-----------------------------------"

    # Check if required files exist
    if [[ ! -f $chunk_align ]]; then
        echo "Chunk alignment file missing: $chunk_align. Skipping chunk."
        continue
    fi

    if [[ ! -f $chunk_tree_stats ]]; then
        echo "Tree stats file missing: $chunk_tree_stats. Skipping chunk."
        continue
    fi

    if [[ ! -f $chunk_tree ]]; then
        echo "Tree file missing: $chunk_tree. Skipping chunk."
        continue
    fi

    # Create FASTA file for sequences to be placed
    if [[ -f $seq_file ]]; then
        echo "Creating FASTA file for sequences to be placed"
        deps/faSomeRecords "$input_fasta" "$seq_file" "$output_fasta"
        
        # Clean up temporary sequence list
        rm "$seq_file"
    else
        echo "Sequences file not found: $seq_file. Skipping chunk."
        continue
    fi

    # Create reference package for pplacer
    echo "Creating reference package for pplacer"
    taxit create -l its -P "$output_refpkg" \
    --aln-fasta "$chunk_align" \
    --tree-stats "$chunk_tree_stats" \
    --tree-file "$chunk_tree"

    # Align to-be-placed sequences with chunk sequences
    echo "Aligning to-be-placed sequences with chunk sequences"
    deps/mafft-linux64/mafft.bat --addfragments "$output_fasta" --keeplength \
    "$chunk_align" > "$output_align" 2> "$mafft_log"

    # Perform placement with pplacer
    echo "Running pplacer"
    deps/pplacer-Linux-v1.1.alpha19/pplacer -c "$output_refpkg" \
    "$output_align" -o "$output_pplacer" > "$pplacer_log"

    # Clean up reference package
    rm -r "$output_refpkg"
done
