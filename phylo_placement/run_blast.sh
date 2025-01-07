#!/bin/bash

# Input file and directories
input_fasta=$1
input_dir=$(dirname "$1")
phylogeny_dir=MDDB-phylogeny/l0.2_s3_4_1500_o1.0_a0_constr_localpair
backbone_blastdb=$phylogeny_dir/backbone_blastdb/backbone
cached_blast_results=$phylogeny_dir/cached_blast_results.txt
cached_no_hits=$phylogeny_dir/cached_no_hits.txt
output_blast=$input_dir/blast_hits.txt
output_no_hits=$input_dir/no_hits.fasta

# Ensure cached results files exist
touch "$cached_blast_results" "$cached_no_hits"

# Step 1: Extract sequence IDs from input FASTA
echo "🔄 Identifying new sequences for BLAST..."

grep '^>' "$input_fasta" | sed 's/^>//' > "$input_dir/all_seq_ids.txt"
total_sequences=$(wc -l < "$input_dir/all_seq_ids.txt")

# Step 2: Check for sequence reuse in caches
# Get sequence IDs from BLAST cache that match the current input
awk -F'\t' '{print $1}' "$cached_blast_results" | sort | uniq > "$input_dir/cached_seq_ids.txt"
grep -Fxf "$input_dir/all_seq_ids.txt" "$input_dir/cached_seq_ids.txt" > "$input_dir/reused_blast_seq_ids.txt"
reused_blast_count=$(wc -l < "$input_dir/reused_blast_seq_ids.txt")

# Get sequence IDs from No-Hit cache that match the current input
sort "$cached_no_hits" > "$input_dir/cached_no_hit_ids.txt"
grep -Fxf "$input_dir/all_seq_ids.txt" "$input_dir/cached_no_hit_ids.txt" > "$input_dir/reused_no_hit_seq_ids.txt"
reused_no_hit_count=$(wc -l < "$input_dir/reused_no_hit_seq_ids.txt")

# Identify sequences not in either cache
grep -Fvxf "$input_dir/reused_blast_seq_ids.txt" "$input_dir/all_seq_ids.txt" | \
grep -Fvxf "$input_dir/reused_no_hit_seq_ids.txt" > "$input_dir/new_seq_ids.txt"
new_seq_count=$(wc -l < "$input_dir/new_seq_ids.txt")

echo "📊 Total sequences: $total_sequences"
echo "📊 Reused BLAST cache hits: $reused_blast_count"
echo "📊 Reused No-Hit cache entries: $reused_no_hit_count"
echo "📊 New sequences to BLAST: $new_seq_count"

# Step 3: Process new sequences with BLAST (if any)
if [[ $new_seq_count -gt 0 ]]; then
    echo "🔍 Extracting new sequences for BLAST..."
    deps/faSomeRecords "$input_fasta" "$input_dir/new_seq_ids.txt" "$input_dir/new_sequences.fasta"
    
    echo "🚀 Running BLAST on $new_seq_count new sequences..."
    blastn -query "$input_dir/new_sequences.fasta" -db "$backbone_blastdb" -out "$input_dir/new_blast_hits.txt" -outfmt 6 -max_target_seqs 10

    # Append new BLAST results to the cache
    cat "$input_dir/new_blast_hits.txt" >> "$cached_blast_results"

    # Identify new no-hit sequences and add them to the cache
    awk -F'\t' '{print $1}' "$input_dir/new_blast_hits.txt" | sort | uniq > "$input_dir/new_blasted_seq_ids.txt"
    grep -Fvxf "$input_dir/new_blasted_seq_ids.txt" "$input_dir/new_seq_ids.txt" >> "$cached_no_hits"

    # Clean temporary files
    rm "$input_dir/new_sequences.fasta" "$input_dir/new_blast_hits.txt" "$input_dir/new_blasted_seq_ids.txt"
else
    echo "✅ No new sequences to BLAST. Using cached results only."
fi

# Step 4: Extract relevant results from the cache for the current input FASTA
echo "🔄 Extracting relevant results for current input..."
grep -Ff "$input_dir/all_seq_ids.txt" "$cached_blast_results" > "$output_blast"

# Step 5: Identify Sequences with No BLAST Hits ###
echo "🔄 Identifying sequences with no BLAST hits..."
awk -F'\t' '{print $1}' "$output_blast" | sort | uniq > "$input_dir/blasted_sequences.txt"
deps/faSomeRecords -exclude "$input_fasta" "$input_dir/blasted_sequences.txt" "$output_no_hits"

# Update no-hit cache with current run results
grep '^>' "$output_no_hits" | sed 's/^>//' >> "$cached_no_hits"
sort -u -o "$cached_no_hits" "$cached_no_hits"

# Step 6: Cleanup Temporary Files ###
rm "$input_dir/all_seq_ids.txt" "$input_dir/cached_seq_ids.txt" "$input_dir/cached_no_hit_ids.txt" \
   "$input_dir/reused_blast_seq_ids.txt" "$input_dir/reused_no_hit_seq_ids.txt" \
   "$input_dir/new_seq_ids.txt" "$input_dir/blasted_sequences.txt"

echo "✅ BLAST process completed. Results saved in: $output_blast"
echo "✅ Sequences with no hits saved in: $output_no_hits"
