import React from "react";

import Spinner from "./Spinner";
import API_URL from "../CalendarAPI";

export default class CreatePage extends React.Component {
  constructor(props) {
    super(props);
    this.state = {
      programs: [],
      courses: [],
      selectedProgram: null,
      selectedCourses: [],
      url: null
    };
  }

  componentDidMount() {
    fetch(API_URL + "/programs")
      .then(response => response.json())
      .then(data => this.setState({ programs: data }));
  }

  _onProgramSelected(e) {
    const program = this.state.programs[e.target.value];
    this.setState({ selectedProgram: program });
    fetch(API_URL + "/courses/" + program.year + "/" + program.id)
      .then(response => response.json())
      .then(data => this.setState({ courses: data }));
  }

  _courseSelected(course) {
    for (let i = 0; i < this.state.selectedCourses.length; ++i) {
      if (this.state.selectedCourses[i].name === course) return true;
    }
    return false;
  }

  _addCourse(course) {
    if (this._courseSelected(course)) return false;

    const selectedCourses = this.state.selectedCourses;
    selectedCourses.push({
      name: course,
      program: this.state.selectedProgram.id,
      year: this.state.selectedProgram.year
    });
    this.setState({ selectedCourses });
  }

  _removeCourse(course) {
    const selectedCourses = this.state.selectedCourses;
    selectedCourses.splice(selectedCourses.indexOf(course), 1);
    this.setState({ selectedCourses });
  }

  _createCalendar() {
    fetch(API_URL + "/calendars/create", {
      method: "POST",
      body: JSON.stringify(this.state.selectedCourses)
    })
      .then(response => response.json())
      .then(data => this.setState({ url: data.url }));
  }

  _renderSelectProgram() {
    return (
      <div className="create-step">
        <h5>Stap 1: Selecteer Richting</h5>
        <select
          className="form-control"
          onChange={this._onProgramSelected.bind(this)}
        >
          <option>Selecteer...</option>
          {this.state.programs.map((program, idx) => {
            return (
              <option key={idx} value={idx}>
                {program.name}
              </option>
            );
          })}
        </select>
      </div>
    );
  }

  _renderSelectCourses() {
    return (
      <div className="create-step">
        <h5>Stap 2: Selecteer Vakken</h5>
        <p>
          Kies de vakken die je aan je gepersonaliseerde calendar wilt
          toevoegen. Herhaal stappen 1 en 2 tot je alle gewenste vakken aan je
          kalender hebt toegevoegd.{" "}
        </p>

        <strong>Let op:</strong>
        <ul>
          <li>
            Het is mogelijk dat hier enkel de vakken verschijnen die in het
            huidige kwartiel/semester gedoceerd worden
          </li>
          <li>
            Sommige vakken worden weergegeven met hun afkorting i.p.v. de
            volledige vaknaam
          </li>
        </ul>

        <table className="table">
          <tbody>
            {this.state.courses.map((course, idx) => {
              if (this._courseSelected(course)) {
                return null;
              }
              return (
                <tr key={idx}>
                  <td>{course}</td>
                  <td>
                    <button
                      type="button"
                      className="btn btn-primary btn-xs float-right"
                      onClick={() => this._addCourse(course)}
                    >
                      Toevoegen
                    </button>
                  </td>
                </tr>
              );
            })}
          </tbody>
        </table>

        <h6>Geselecteerde Vakken:</h6>
        {this.state.selectedCourses.length === 0 ? (
          <p>
            <i>Nog geen vakken gekozen.</i>
          </p>
        ) : (
          <table className="table">
            <tbody>
              {this.state.selectedCourses.map((course, idx) => {
                return (
                  <tr key={idx}>
                    <td>{course.name}</td>
                    <td>
                      <button
                        type="button"
                        className="btn btn-danger btn-xs float-right"
                        onClick={() => {
                          this._removeCourse(course);
                        }}
                      >
                        Verwijder
                      </button>
                    </td>
                  </tr>
                );
              })}
            </tbody>
          </table>
        )}
      </div>
    );
  }

  _renderCreateCalendar() {
    return (
      <div className="create-step">
        <h5>Stap 3: Creëer Calendar</h5>
        <p>
          Wanneer je al je vakken gekozen hebt, klik je op onderstaande knop om
          je gepersonaliseerde calendar aan te maken.
        </p>
        <p>
          <button
            type="button"
            className="btn btn-primary"
            onClick={this._createCalendar.bind(this)}
          >
            Calendar Aanmaken
          </button>
        </p>
        {this.state.url == null ? null : (
          <p>
            Voila! Deze iCal kan je toevoegen in je Google of iCloud calendar:<br />
            <code>{this.state.url}</code>
          </p>
        )}
      </div>
    );
  }

  render() {
    if (this.state.programs.length === 0) {
      return (
        <div className="content">
          <Spinner />
        </div>
      );
    }
    return (
      <div className="content">
        {this._renderSelectProgram()}
        {this._renderSelectCourses()}
        {this._renderCreateCalendar()}
      </div>
    );
  }
}
