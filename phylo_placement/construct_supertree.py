import os
import re
import json


def load_representatives_tree(representatives_tree_path):
    """ Load the representatives tree Newick file """
    with open(representatives_tree_path, "r") as f:
        return f.read().strip()


def load_jplace_file(jplace_path):
    """Extract the clade without the OUTGROUP from a jplace file."""
    with open(jplace_path, "r") as f:
        jplace_data = json.load(f)

    tree_str = jplace_data["tree"]

    # Extract clade excluding the OUTGROUP using regex
    match = re.search(r"\((\(.+\)).+OUTGROUP", tree_str)
    if match:
        subtree = match.group(1)
    else:
        subtree = tree_str  # If OUTGROUP is missing, take full tree

    return subtree, jplace_data["placements"], jplace_data["version"], jplace_data["fields"]


def prefix_edge_numbers(tree, chunk_id):
    """ Prefix edge numbers in the subtree with the chunk ID to make them unique """
    return re.sub(r"{(\d+)}", lambda m: f"{{{int(chunk_id)}0{m.group(1)}}}", tree)


def replace_subtree(supertree, chunk_id, subtree):
    """ Replace a subtree in the supertree with the subtree with unique edge numbers """
    # Find and replace in supertree
    supertree = re.sub(fr"\({chunk_id}_.+?\)", subtree, supertree, count=1)

    return supertree


def main(input_fasta_path):
    # Define paths
    base_dir = os.path.dirname(input_fasta_path)
    placement_dir = os.path.join(base_dir, "placement_output")
    representatives_tree_path = "MDDB-phylogeny/l0.2_s3_4_1500_o1.0_a0_constr_localpair/supertree/representatives_tree.tre"

    # Load supertree
    supertree = load_representatives_tree(representatives_tree_path)

    # Collect all placements
    combined_placements = []

    # Process each chunk
    for chunk_folder in os.listdir(placement_dir):
        chunk_path = os.path.join(placement_dir, chunk_folder)
        if not os.path.isdir(chunk_path):
            continue

        # Extract chunk ID (first part of the folder name)
        chunk_id = chunk_folder.split("_")[0]
        jplace_path = os.path.join(chunk_path, "placements.jplace")

        if not os.path.exists(jplace_path):
            continue

        print(f"🔄 Processing {chunk_folder}")

        # Load subtree and placements from jplace
        subtree, placements, version, fields = load_jplace_file(jplace_path)

        # Prefix edge numbers in the subtree
        subtree = prefix_edge_numbers(subtree, chunk_id)

        # Replace the subtree in the representatives tree
        supertree = replace_subtree(supertree, chunk_id, subtree)

        # Append placements with modified edge numbers
        for p in placements:
            p_del = []
            for i, (_, edge_num, *rest) in enumerate(p["p"]):
                full_edge_num = int(f"{chunk_id}0{edge_num}")
                if f"{{{full_edge_num}}}" in supertree:
                    p["p"][i][1] = full_edge_num
                else:
                    p_del.append(i)
            for i in sorted(p_del, reverse=True):
                del p["p"][i]
            combined_placements.append(p)

    # Create new jplace file
    output_jplace = {
        "tree": supertree,
        "placements": combined_placements,
        "version": version,
        "fields": fields
    }

    # Save output jplace file
    output_path = os.path.join(base_dir, "supertree_placements.jplace")
    with open(output_path, "w") as f:
        json.dump(output_jplace, f, indent=2)

    print(f"✅ Combined jplace file saved to {output_path}")


if __name__ == "__main__":
    import sys
    if len(sys.argv) != 2:
        print("Usage: python combine_placements.py <input.fasta>")
        sys.exit(1)
    main(sys.argv[1])
