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
alignment_cache_dir=$phylogeny_dir/alignment_cache

# Ensure required directories exist
mkdir -p "$alignment_cache_dir"
mkdir -p "$output_dir"

echo "🔄 Identifying cached and new sequences for placement..."
awk -F',' 'NR > 1 {print $2 "," $1}' "$chunk_agg_csv" | while IFS=',' read -r chunk seq_id; do
    chunk_dir="$output_dir/$chunk"
    mkdir -p "$chunk_dir"

    chunk_cache="$alignment_cache_dir/$chunk.fasta"

    if [[ -f $chunk_cache ]] && grep -q "^>$seq_id\$" "$chunk_cache"; then
        echo "$seq_id" >> "$chunk_dir/reused_seq_ids.txt"
    else
        echo "$seq_id" >> "$chunk_dir/new_seq_ids.txt"
    fi
done

# Process each chunk
for chunk_dir in "$output_dir"/*; do
    output_align="$chunk_dir/placed_seqs_aligned.fasta"
    output_pplacer="$chunk_dir/placements.jplace"

    mafft_log="$chunk_dir/mafft.log"
    pplacer_log="$chunk_dir/pplacer.log"

    chunk_base_name=$(basename "$chunk_dir")
    chunk_base_name_num=${chunk_base_name::3}

    chunk_align="$chunk_align_dir/$chunk_base_name.fasta"
    chunk_tree_stats="$chunk_tree_dir/RAxML_info.$chunk_base_name_num.out"
    chunk_tree="$chunk_tree_dir/RAxML_bestTree.$chunk_base_name_num.out"
    chunk_cache="$alignment_cache_dir/$chunk_base_name.fasta"

    echo ""
    echo "🧩 Processing Chunk: $chunk_base_name"
    echo "-----------------------------------"

    if [[ ! -f $chunk_align || ! -f $chunk_tree_stats || ! -f $chunk_tree ]]; then
        echo "⚠️  Missing essential files for chunk: $chunk_base_name. Skipping."
        continue
    fi

    # Count cached sequences
    if [[ -f "$chunk_dir/reused_seq_ids.txt" ]]; then
        deps/faSomeRecords "$chunk_cache" "$chunk_dir/reused_seq_ids.txt" "$chunk_dir/reused_seqs.fasta"
        cached_count=$(wc -l < "$chunk_dir/reused_seq_ids.txt")
    else
        touch "$chunk_dir/reused_seqs.fasta"
        cached_count=0
    fi

    # Count new sequences
    if [[ -f "$chunk_dir/new_seq_ids.txt" ]]; then
        deps/faSomeRecords "$input_fasta" "$chunk_dir/new_seq_ids.txt" "$chunk_dir/new_seqs.fasta"
        new_count=$(wc -l < "$chunk_dir/new_seq_ids.txt")
    else
        touch "$chunk_dir/new_seqs_aligned.fasta"
        new_count=0
    fi

    echo "📊 Cached sequences: $cached_count"
    echo "📊 New sequences: $new_count"

    # Align new sequences if needed
    if [[ $new_count -gt 0 ]]; then
        echo "🛠️  Aligning $new_count new sequences with MAFFT..."
        deps/mafft-linux64/mafft.bat --addfragments "$chunk_dir/new_seqs.fasta" --keeplength \
        "$chunk_align" > "$chunk_dir/new_seqs_aligned_with_chunk.fasta" 2> "$mafft_log"

        deps/faSomeRecords "$chunk_dir/new_seqs_aligned_with_chunk.fasta" "$chunk_dir/new_seq_ids.txt" "$chunk_dir/new_seqs_aligned.fasta"

        # Append new sequences to cache
        cat "$chunk_dir/new_seqs_aligned.fasta" >> "$chunk_cache"
    fi

    echo "📂 Creating final alignment file..."
    cat "$chunk_align" "$chunk_dir/reused_seqs.fasta" "$chunk_dir/new_seqs_aligned.fasta" > "$output_align"

    echo "🚀 Running pplacer..."
    taxit create -l its -P "$chunk_dir/refpkg.refpkg" \
        --aln-fasta "$chunk_align" --tree-stats "$chunk_tree_stats" --tree-file "$chunk_tree"

    deps/pplacer-Linux-v1.1.alpha19/pplacer -c "$chunk_dir/refpkg.refpkg" \
        "$output_align" -o "$output_pplacer" > "$pplacer_log"

    # Cleanup
    rm -r "$chunk_dir/refpkg.refpkg"
    rm -f "$chunk_dir/new_seq_ids.txt" "$chunk_dir/reused_seq_ids.txt"
    rm -f "$chunk_dir/reused_seqs.fasta" "$chunk_dir/new_seqs.fasta" "$chunk_dir/new_seqs_aligned_with_chunk.fasta" "$chunk_dir/new_seqs_aligned.fasta"

    echo "✅ Finished processing chunk: $chunk_base_name"
done

echo "🎉 Phylogenetic placement complete!"
