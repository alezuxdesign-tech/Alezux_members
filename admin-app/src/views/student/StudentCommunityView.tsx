import React from "react";
import { Course } from "../../services/api";
import { DiscordCommunityView } from "./DiscordCommunityView";

export interface StudentCommunityViewProps {
  courses: Course[];
  academyName: string;
  isAdmin?: boolean;
  currentUser?: {
    id: number;
    name: string;
    avatar?: string;
    role?: string;
  };
  onNavigateToCourse?: (courseId: number) => void;
}

export const StudentCommunityView: React.FC<StudentCommunityViewProps> = ({
  courses,
  academyName,
  isAdmin = false,
  currentUser,
  onNavigateToCourse,
}) => {
  return (
    <DiscordCommunityView
      courses={courses}
      academyName={academyName}
      isAdmin={isAdmin}
      currentUser={currentUser}
      onNavigateToCourse={onNavigateToCourse}
    />
  );
};

export default StudentCommunityView;
