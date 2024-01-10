import sys
import json
import numpy as np
from sklearn.feature_extraction.text import TfidfVectorizer
from sklearn.metrics.pairwise import cosine_similarity

try:
    # Get the file path from command-line arguments
    if len(sys.argv) < 2:
        print("Usage: python new-recommendation.py <file_path>")
        sys.exit(1)

    file_path = sys.argv[1]

    # Read the JSON data from the file
    with open(file_path, 'r') as file:
        data = file.read()

    # Attempt to parse the JSON data
    data_dict = json.loads(data)

    # Extract user preferences and dataset
    user_preferences = data_dict.get("user_preferences", [])
    dataset = data_dict.get("dataset", [])

    # Check if user_preferences and dataset are not empty
    if not user_preferences:
        print("Error: Empty user preferences")
        sys.exit(1)

    if not dataset:
        print("Error: Empty dataset")
        sys.exit(1)

    # Combine user preferences and dataset into a list of documents
    documents = user_preferences + [entry.get("technology_line", "") for entry in dataset]

    # Create a TF-IDF vectorizer to convert text data into numerical form
    vectorizer = TfidfVectorizer()
    tfidf_matrix = vectorizer.fit_transform(documents)

    # Calculate pairwise cosine similarities
    similarities = cosine_similarity(tfidf_matrix)

    # Find the most similar technology line for each user preference
    user_preference_scores = similarities[:len(user_preferences), len(user_preferences):]

    # Find the technology line index with the highest similarity for each user preference
    best_matches = np.argmax(user_preference_scores, axis=1)

    # Create a list to store the results
    results = []

    # Prepare and store the results
    for i, preference in enumerate(user_preferences):
        best_match_index = best_matches[i]
        best_match_entry = dataset[best_match_index]
        result = {
            "User Preference": preference,
            "Best Match Technology Line": best_match_entry.get("technology_line", ""),
            "Session ID": best_match_entry.get("session_id", ""),
            "Session Title": best_match_entry.get("session_title", ""),  # Include session_title field
            "Date": best_match_entry.get("date", ""),
            "Timeam": best_match_entry.get("timeam", ""),
            "Timepm": best_match_entry.get("timepm", ""),
            "Speaker": best_match_entry.get("speaker", ""),
            "Cosine Similarity Score": user_preference_scores[i, best_match_index],
        }
        results.append(result)

    # Convert the results to JSON format
    results_json = json.dumps(results)

    # Print the JSON results
    print(results_json)

except Exception as e:
    print("Error:", str(e))
    sys.exit(1)
