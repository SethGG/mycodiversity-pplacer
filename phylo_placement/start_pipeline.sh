#!/bin/bash

FASTA_FILE=$(realpath "$1")

echo "----------------------------------------"
echo "- Step 1: BLAST against reference tree -"
echo "----------------------------------------"
echo ""

bash run_blast.sh $FASTA_FILE

echo ""
echo "--------------------------------------- "
echo "- Step 2: Determining majority chunks -"
echo "--------------------------------------- "
echo ""

bash determine_chunks.sh $FASTA_FILE

echo ""
echo "----------------------------------------------"
echo "- Step 3: Phylogenetic placement in subtrees -"
echo "----------------------------------------------"

#bash perform_placement.sh $FASTA_FILE

echo "Placement completed successfully!"
