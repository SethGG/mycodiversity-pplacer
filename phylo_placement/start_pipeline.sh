#!/bin/bash

FASTA_FILE=$(realpath "$1")

# Function to calculate elapsed time
time_step() {
    local start_time=$1
    local end_time=$2
    local elapsed=$((end_time - start_time))
    printf "Step completed in %02d:%02d:%02d (HH:MM:SS)\n\n" $((elapsed/3600)) $(((elapsed%3600)/60)) $((elapsed%60))
}

echo "----------------------------------------"
echo "- Step 1: BLAST against reference tree -"
echo "----------------------------------------"
echo ""

start_time=$(date +%s)
bash run_blast.sh "$FASTA_FILE"
end_time=$(date +%s)
time_step $start_time $end_time

echo "--------------------------------------- "
echo "- Step 2: Determining majority chunks -"
echo "--------------------------------------- "
echo ""

start_time=$(date +%s)
bash determine_chunks.sh "$FASTA_FILE"
end_time=$(date +%s)
time_step $start_time $end_time

echo "----------------------------------------------"
echo "- Step 3: Phylogenetic placement in subtrees -"
echo "----------------------------------------------"

start_time=$(date +%s)
bash perform_placement.sh "$FASTA_FILE"
end_time=$(date +%s)
time_step $start_time $end_time

echo "----------------------------------"
echo "- Step 4: Constructing supertree -"
echo "----------------------------------"

start_time=$(date +%s)
python3 construct_supertree.py "$FASTA_FILE"
end_time=$(date +%s)
time_step $start_time $end_time

echo "Placement completed successfully!"
