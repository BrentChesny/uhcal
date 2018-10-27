import React from "react";
import { Link } from "react-router-dom";

export default class HomePage extends React.Component {
  render() {
    return (
      <div className="content">
        <div className="jumbotron">
          <h1>Jouw uurrooster op maat</h1>
          <p className="lead">
            Gebruik deze tool om makkelijk een gepersonaliseerde iCal te maken
            wanneer je vakken volgt uit verschillende richtingen.
          </p>
          <p>
            <Link className="btn btn-lg btn-success" role="button" to="/create">
              Creëer Persoonlijke iCal
            </Link>
          </p>
        </div>
      </div>
    );
  }
}
