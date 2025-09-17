# -------------------------------
# Step 1 – Store Data Structures
# -------------------------------

# List for Ages
ages = [20, 21, 20, 21, 9]
print("Ages:", ages)

# Dictionary for Favorite Subjects
# Key = Student name, Value = Favorite subject
fav_subjects = {
    "Sullivan D. Pomer": "Software Engineering",
    "Kisses Dela Cruz": "English",
    "Roudgie Bonagua": "Data Analysis",
    "Gwyneth Linga": "Science",
    "Enriquez, John Carlo S.": "Python"
}
print("Favorite Subjects:", fav_subjects)

# Tuple for (Name, Age) pairs (unchanging info)
student_info = (
    ("Sullivan D. Pomer", 20),
    ("Kisses Dela Cruz", 21),
    ("Roudgie Bonagua", 20),
    ("Gwyneth Linga", 21),
    ("Enriquez, John Carlo S.", 9)
)
print("Student Info:", student_info)

# -------------------------------
# Step 2 – Analyze Data (NumPy)
# -------------------------------
import numpy as np

# 1. Calculate average age
avg_age = np.mean(ages)
print("Average Age:", avg_age)

# 2. Calculate average study hours
study_hours = [2, 2, 1, 3, 3]
avg_study_hours = np.mean(study_hours)
print("Average Study Hours:", avg_study_hours)

# -------------------------------
# Step 3 – Analyze Data (pandas)
# -------------------------------
import pandas as pd

# 3. Create DataFrame with all collected data
data = {
    "Name": ["Sullivan D. Pomer", "Kisses Dela Cruz", "Roudgie Bonagua", "Gwyneth Linga", "Enriquez, John Carlo S."],
    "Age": [20, 21, 20, 21, 9],
    "Favorite Subject": ["Software Engineering", "English", "Data Analysis", "Science", "Python"],
    "Study Hours/Day": [2, 2, 1, 3, 3],
    "Note": ["", "", "Pogi: Sobra", "", ""]
}
df = pd.DataFrame(data)
print("\nClassmate DataFrame:\n", df)

# 4. Count how many students prefer each subject
subject_counts = df["Favorite Subject"].value_counts()
print("\nSubject Preferences:\n", subject_counts)
