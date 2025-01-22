#!/bin/bash

# Input file and directories
input_fasta=$1
input_dir=$(dirname "$1")
phylogeny_dir=MDDB-phylogeny/l0.2_s3_4_1500_o1.0_a0_constr_localpair
chunk_dir=$phylogeny_dir/chunks/unaligned
chunk_cache=$phylogeny_dir/chunk_cache.txt

output_blast=$input_dir/blast_hits.txt
output_blast_chunks=$input_dir/blast_hits_chunks.csv
output_blast_chunks_agg=$input_dir/blast_hits_chunks_agg.csv
output_summary=$input_dir/chunk_summary.csv
output_no_hits=$input_dir/no_hits.fasta

# Ensure cache file exists
touch "$chunk_cache"

### Step 1: Build Chunk Cache for BLAST Hits ###
echo "🔄 Processing BLAST hits to match chunks with caching..."
echo "refsequence_pk,identity,UNITE_id,chunk" > "$output_blast_chunks"

# Create temporary files
awk -F'\t' '{print $2}' "$output_blast" | sort | uniq > "$input_dir/blast_unite_ids.txt"

# Track statistics
cached_hits=0
new_hits=0

# Match each UNITE_id to a chunk
while read -r unite_id; do
    # Check if UNITE_id is already in the cache
    cached_chunk=$(grep -m1 "^$unite_id," "$chunk_cache" | cut -d',' -f2)
    
    if [[ -n "$cached_chunk" ]]; then
        # Cached result found
        ((cached_hits++))
        chunk=$cached_chunk
    else
        # Not in cache, perform grep
        chunk_file=$(grep -l "$unite_id" "$chunk_dir"/*.fasta)
        ((new_hits++))
        
        if [[ -n "$chunk_file" ]]; then
            chunk=$(basename "$chunk_file" .fasta)
        else
            chunk="discarded"
        fi
        
        # Store result in cache
        echo "$unite_id,$chunk" >> "$chunk_cache"
    fi
    
    # Write to blast_hits_chunks
    awk -v id="$unite_id" -v chunk="$chunk" -F'\t' '$2 == id {print $1 "," $3 "," $2 "," chunk}' "$output_blast" >> "$output_blast_chunks"
done < "$input_dir/blast_unite_ids.txt"

echo "📊 Cached matches reused: $cached_hits"
echo "📊 New matches added to cache: $new_hits"

# Cleanup temporary files
rm "$input_dir/blast_unite_ids.txt"

### Step 2: Aggregate chunk statistics ###
echo "🔄 Aggregating chunk statistics..."
echo "refsequence_pk,majority_chunk,majority_count,unique_chunks" > "$output_blast_chunks_agg"

tail -n +2 "$output_blast_chunks" | sort -t',' -k1,1 | awk -F',' '
    NR > 1 {
        refsequence_pk = $1
        chunk = $4

        if (refsequence_pk != prev_seq && NR > 2) {
            # Output previous sequence stats
            process_sequence(prev_seq, chunk_count)
            delete chunk_count
        }
        
        chunk_count[chunk]++
        prev_seq = refsequence_pk
    }
    END {
        if (NR > 1) {
            # Handle the last sequence
            process_sequence(prev_seq, chunk_count)
        }
    }

    function process_sequence(seq, count_arr) {
        maj_chunk = ""
        maj_count = 0
        unique_chunks = 0

        for (ch in count_arr) {
            unique_chunks++
            if (count_arr[ch] > maj_count) {
                maj_count = count_arr[ch]
                maj_chunk = ch
            }
        }
        print seq "," maj_chunk "," maj_count "," unique_chunks
    }
' >> "$output_blast_chunks_agg"

### Step 3: Generate Summary ###
echo "🔄 Generating chunk summary..."
echo "sequences,chunk" > "$output_summary"

# Count majority chunks from aggregated statistics
awk -F',' '
    NR > 1 {
        chunk_count[$2]++
    }
    END {
        for (chunk in chunk_count) {
            print chunk_count[chunk] "," chunk
        }
    }
' "$output_blast_chunks_agg" >> "$output_summary"

# Add sequences without hits to the summary
no_hit_count=$(grep -c '^>' "$output_no_hits")
echo "$no_hit_count,none" >> "$output_summary"

# Sort the summary CSV by sequence count in descending order
(head -n 1 "$output_summary" && tail -n +2 "$output_summary" | sort -t',' -k1,1nr) > "$output_summary.tmp" && mv "$output_summary.tmp" "$output_summary"

### Step 4: Display Summary ###
echo
echo "Chunk Summary (Sorted by Sequence Count):"
printf "%-7s %-20s\n" "Count" "Chunk"
printf "%-7s %-20s\n" "-------" "--------------------"

awk -F',' 'NR > 1 { printf "%-7s %-20s\n", $1, $2 }' "$output_summary"
